<?php

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../..' );
define('JPATH_CORE', JPATH_BASE . '/../..');

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );

$mainframe =& JFactory::getApplication('site');
$jcfg =& JFactory::getConfig();
$db  =& JFactory::getDBO();
$user = &JFactory::getUser();
$date = new JDate('now');

JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

$data = JRequest::getVar('jform', array(), 'post', 'array');
$pollid  = JRequest::getVar('poll');
$showresults  = JRequest::getVar('showresults');
$showresultslink  = JRequest::getVar('showresultslink');
$resultsas  = JRequest::getVar('resultsas');
$resultslink  = urldecode(JRequest::getVar('resultslink'));

// Get poll data
$pquery = $db->getQuery(true);
$pquery->select('*');
$pquery->from('#__mpoll_polls');
$pquery->where('poll_id = '.$pollid);
$pquery->where('published > 0');
$db->setQuery( $pquery );
$pdata = $db->loadObject();

// Save completed
$cmrec=new stdClass();
$cmrec->cm_user=$user->id;
$cmrec->cm_poll=$pollid;
$cmrec->cm_useragent=$_SERVER['HTTP_USER_AGENT'];
$cmrec->cm_ipaddr=$_SERVER['REMOTE_ADDR'];
$db->insertObject('#__mpoll_completed',$cmrec);
$subid = $db->insertid();

// Get questions

$qquery=$db->getQuery(true);
$qquery->select('*');
$qquery->from('#__mpoll_questions');
$qquery->where('published > 0');
$qquery->where('q_poll = '.$pollid);
$qquery->where('q_type IN ("mcbox","mlist","mailchimp","email","dropdown","multi","cbox","textbox","textar")');
$qquery->order('ordering ASC');
$db->setQuery( $qquery );
$qdata = $db->loadObjectList();

// Add options to questions and format params
foreach ($qdata as &$q) {
	if ($q->q_type == "multi" || $q->q_type == "mcbox" || $q->q_type == "dropdown" || $q->q_type == "mlist") {
		$qo=$db->getQuery(true);
		$qo->select('opt_txt as text, opt_id as value, opt_disabled, opt_correct, opt_color, opt_other, opt_selectable');
		$qo->from('#__mpoll_questions_opts');
		$qo->where('opt_qid = '.$q->q_id);
		$qo->where('published > 0');
		$qo->order('ordering ASC');
		$db->setQuery($qo);
		$q->options = $db->loadObjectList();
	}
	$registry = new JRegistry();
	$registry->loadString($q->params);
	$q->params = $registry->toObject();
}


try {
	// Process $data
	$item = new stdClass();
	$other = new stdClass();
	$fids = array();
	$optfs = array();
	$moptfs = array();
	$upfile=array();
	$mclists = array();
	foreach ($qdata as $d) {
		$fieldname = 'q_'.$d->q_id;
		if ($d->q_type == 'attach') {
			$upfile[]=$fieldname;
		} else if ($d->q_type == 'mailchimp') {
			$mclists[]=$d;
		} else if ($d->q_type=="mcbox" || $d->q_type=="mlist") {
			if (is_array($data[$fieldname])) $item->$fieldname = implode(" ",$data[$fieldname]);
			else $item->$fieldname = "";
		} else if ($d->q_type=='cbox') {
			$item->$fieldname = ($data[$fieldname]=='on') ? "1" : "0";
		} else {
			$item->$fieldname = $data[$fieldname];
		}
		if ($d->q_type=="multi" || $d->q_type=="dropdown") {
			$optfs[]=$fieldname;
			$other->$fieldname=$data[$fieldname."_other"];
		}
		if ($d->q_type=="mcbox" || $d->q_type=="mlist") {
			$moptfs[]=$fieldname;
			$other->$fieldname=$data[$fieldname."_other"];
		}
		$fids[]=$d->uf_id;
	}

	// Get Options
	$odsql=$db->getQuery(true);
	$odsql->select('*');
	$odsql->from('#__mpoll_questions_opts');
	$db->setQuery($odsql);
	$optionsdata = array();
	$optres = $db->loadObjectList();
	foreach ($optres as $o) {
		$optionsdata[$o->opt_id]=$o->opt_txt;
	}

	// MailChimp List
	foreach ($mclists as $mclist) {
		if ($data['q_'.$mclist->q_id])  {
			if (strstr($mclist->q_default,"_")){ list($mc_key, $mc_list) = explode("_",$mclist->q_default,2);	}
			$mcf='q_'.$mclist->q_id;
			include_once '../../components/com_mpoll/lib/mailchimp.php';
			$mc = new MailChimpHelper($mc_key,$mc_list);
			$mcdata = array('OPTIN_IP'=>$_SERVER['REMOTE_ADDR'], 'OPTIN_TIME'=>$date->toSql(true));
			$email = $data['q_'.$mclist->params->mc_emailfield];
			if ($mclist->params->mcvars) {
				$othervars=$mclist->params->mcvars;
				foreach ($othervars as $mcv=>$qfid) {
					$qf='q_'.$qfid;
					if ($qfid) {
						if (in_array($qf,$optfs)) {
							$mcdata[$mcv] = $optionsdata[$item->$qf];
						} else if (in_array($qf,$moptfs)) {
							$mcdata[$mcv] = "";
							foreach (explode(" ",$item->$qf) as $mfo) {
								$mcdata[$mcv] .= $optionsdata[$mfo]." ";
							}
						} else {
							$mcdata[$mcv] = $item->$qf;
						}
					}
				}
			}
			if (!$mc->subStatus($email)) {
				$mcresult = $mc->subscribeUser(array("email"=>$email),$mcdata,(bool)$mclist->params->mc_doubleoptin,"html");
				if ($mcresult) { $item->$mcf=$mclist->params->mc_doubleoptin ? "Op-In Sent" : "Subscribed"; }
				else { $item->$mcf=$mc->error; }
			} else {
				$item->$mcf="Already Subscribed";
			}
		} else {
			$mcf='q_'.$mclist->q_id;
			$item->$mcf="Not Subscribed";
		}
	}

	// Save results
	foreach ($qdata as $fl) {
		$fieldname = 'q_'.$fl->q_id;
		if ($fl->q_type != "captcha") {
			$cmres=new stdClass();
			$cmres->res_user=$user->id;
			$cmres->res_poll=$pollid;
			$cmres->res_qid=$fl->q_id;
			$cmres->res_ans=$db->escape($item->$fieldname);
			$cmres->res_cm=$subid;
			$cmres->res_ans_other=$db->escape($other->$fieldname);
			$db->insertObject('#__mpoll_results',$cmres);
		}
	}
}
catch (Exception $e)
{
	echo $e->getMessage();

	return false;
}

// Show before results message
if ($pdata->poll_results_msg_before) echo $pdata->poll_results_msg_before;


// Show results
if ($pdata->poll_showresults && $showresults) {
	foreach ($qdata as $qr) {
		if ($qr->q_type == "mcbox" || $qr->q_type == "multi" || $qr->q_type == "dropdown" || $qr->q_type == "mlist") {
			echo '<div class="mpollmod-question">';
			$anscor=false;
			echo '<div class="mpollmod-question-text">'.$qr->q_text.'</div>';
			switch ($qr->q_type) {
				case 'multi':
				case 'mcbox':
				case 'mlist':
				case 'dropdown':
					$numr=0;
					foreach ($qr->options as &$o) {
						$qa = $db->getQuery(true);
						$qa->select('count(*)');
						$qa->from('#__mpoll_results');
						$qa->where('res_qid = '.$qr->q_id);
						$qa->where('res_ans LIKE "%'.$o->value.'%"');
						$qa->group('res_qid');
						$db->setQuery($qa);
						$o->anscount = (int)$db->loadResult();
						$numr = $numr + $o->anscount;
					}
					foreach ($qr->options as $opts) {
						if ($opts->opt_selectable) {
							if ($numr != 0) $per = ($opts->anscount)/($numr); else $per=1;
							echo '<div class="mpollmod-opt">';

							echo '<div class="mpollmod-opt-text">';
							if ($opts->opt_correct) echo '<div class="mpollmod-opt-correct">'.$opts->text.'</div>';
							else echo $opts->text;
							echo '</div>';
							echo '<div class="mpollmod-opt-count">';
							if ($resultsas == "percent") echo (int)($per*100)."%";
							else echo ($opts->anscount);
							echo '</div>';
							echo '<div class="mpollmod-opt-bar-box"><div class="mpollmod-opt-bar-bar" style="background-color: '.$opts->opt_color.'; width:'.($per*100).'%"></div></div>';
							echo '</div>';
						}
					}
					break;
				default: break;
			}
			echo '</div>';
		}
	}
}

// Show after results module message
if ($pdata->poll_results_msg_mod) echo $pdata->poll_results_msg_mod;
if ($showresultslink) {
	echo '<p align="center"><a href="'.$resultslink.'" class="button">Results</a></p>';
}