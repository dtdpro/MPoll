<?php

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../..' );
define('JPATH_CORE', JPATH_BASE . '/../..');

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/framework.php' );
require_once ( JPATH_BASE .'/components/com_mpoll/helpers/mpoll.php' );

$mainframe = JFactory::getApplication('site');
$jconfig = JFactory::getConfig();
$db  = JFactory::getDBO();
$user = JFactory::getUser();
$date = new JDate('now');
$cfg = MPollHelper::getConfig();

JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

$data = JRequest::getVar('jform', array(), 'post', 'array');
$pollid  = JRequest::getVar('poll');
$showresults  = JRequest::getVar('showresults');
$showresultslink  = JRequest::getVar('showresultslink');
$resultsas  = JRequest::getVar('resultsas');
$resultslink  = urldecode(JRequest::getVar('resultslink'));
$paylink  = urldecode(JRequest::getVar('paylink'));

// Get poll data
$pquery = $db->getQuery(true);
$pquery->select('*');
$pquery->from('#__mpoll_polls');
$pquery->where('poll_id = '.$pollid);
$pquery->where('published > 0');
$db->setQuery( $pquery );
$pdata = $db->loadObject();

if (property_exists($pdata, 'poll_results_emails') && $pdata->poll_results_emails !== null)
{
	$pdata->poll_results_emails = json_decode($pdata->poll_results_emails,true);
}

// reCAPTCHA
if ($pdata->poll_recaptcha) {
	$rc_url = 'https://www.google.com/recaptcha/api/siteverify';
	$rc_data = array(
		'secret' => $cfg->rc_api_secret,
		'response' => $_POST["g-recaptcha-response"]
	);
	$rc_options = array(
		'http' => array (
			'method' => 'POST',
			'content' => http_build_query($rc_data)
		)
	);
	$rc_context  = stream_context_create($rc_options);
	$rc_verify = file_get_contents($rc_url, false, $rc_context);
	$rc_captcha_success=json_decode($rc_verify);
	if ($rc_captcha_success->success==false) {
		echo '<div class="uk-alert uk-alert-danger" uk-alert=""><h3>Error</h3><p>reCAPTCHA Response Required</p></div>';
		die;
	} else if ($rc_captcha_success->success==true) {

	} else {
		echo '<div class="uk-alert uk-alert-danger" uk-alert=""><h3>Error</h3><p>reCAPTCHA Error</p></div>';
		die;
	}
}

// Save completed
$pubid = bin2hex(random_bytes(16));
$cmrec=new stdClass();
$cmrec->cm_user=$user->id;
$cmrec->cm_poll=$pollid;
$cmrec->cm_pubid=$pubid;
if ($pdata->poll_payment_enabled) $cmrec->cm_status="unpaid";
else $cmrec->cm_status="completed";
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
$qquery->where('q_type IN ("mcbox","mlist","email","dropdown","multi","cbox","textbox","textar","attach")');
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
	$jinput = JFactory::getApplication()->input;
	$item = new stdClass();
	$other = new stdClass();
	$optfs = array();
	$moptfs = array();
	$upfile=array();
	foreach ($qdata as $d) {
		$fieldname = 'q_'.$d->q_id;
		if ($d->q_type == 'attach') {
			$upfile[]=$fieldname;
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
			if (isset($data[$fieldname."_other"])) $other->$fieldname=$data[$fieldname."_other"];
			else $other->$fieldname="";
		}
		if ($d->q_type=="mcbox" || $d->q_type=="mlist") {
			$moptfs[]=$fieldname;
			if (isset($data[$fieldname."_other"])) $other->$fieldname=$data[$fieldname."_other"];
			else $other->$fieldname="";
		}
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

	// Upload files
	foreach ($upfile as $u) {
		$config		= JFactory::getConfig();
		$userfiles = $jinput->files->get($u, array(), 'array');
		$uploaded_files = array();
		foreach ($userfiles as $uf) {
			if (!$uf['error']) {
				// Build the appropriate paths
				$tmp_dest = JPATH_BASE . '/media/com_mpoll/upload/' . $subid . "_" . str_replace( "q_",
						"",
						$u ) . "_" . $uf['name'];
				$tmp_src  = $uf['tmp_name'];

				// Move uploaded file
				jimport( 'joomla.filesystem.file' );
				if ( canUpload( $uf, $err ) ) {
					$uploaded = JFile::upload( $tmp_src, $tmp_dest );
					$uploaded_files[] = '/media/com_mpoll/upload/' . $subid . "_" . str_replace( "q_",
							"",
							$u ) . "_" . $uf['name'];
				} else {
					$this->setError( $err );

					return false;
				}
			}
		}
		if (count($uploaded_files)) $item->$u = implode(",",$uploaded_files);
		else $item->$u = "";
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
			if (isset($other->$fieldname)) {
				$cmres->res_ans_other = $db->escape( $other->$fieldname );
			}
			$db->insertObject('#__mpoll_results',$cmres);
		}
	}

	// Results email
	if ($pdata->poll_resultsemail) {
		$resultsemail = 'A submission has been made to the form <strong>'.$pdata->poll_name.'</strong> with  ID #<strong>'.$subid.'</strong> Submission data is below.<br /><br />';
		$requery=$db->getQuery(true);
		$requery->select('*');
		$requery->from('#__mpoll_questions');
		$requery->where('published > 0');
		$requery->where('q_poll = '.$pollid);
		$requery->where('q_type IN ("mcbox","mlist","email","dropdown","multi","cbox","textbox","textar")');
		$requery->order('ordering ASC');
		$db->setQuery( $requery );
		$flist = $db->loadObjectList();
		foreach ($flist as $d) {
			if ($d->q_type != "captcha" && $d->q_type != "message" && $d->q_type != "header") {
				$fieldname = 'q_'.$d->q_id;
				$resultsemail .= "<b>".$d->q_text.'</b><br />';
				if ($d->q_type=="attach") {
					if($item->$fieldname) $resultsemail .= '<a href="'.JURI::base(  ).$item->$fieldname.'">Download</a>';
				} else if (in_array($fieldname,$optfs)) {
					$resultsemail .= $optionsdata[$item->$fieldname];
					if ($other->$fieldname) $resultsemail .= ': '.$other->$fieldname;
					$resultsemail .= '<br />';
					$resultsemail .= '<br />';
				}  else if (in_array($fieldname,$moptfs)) {
					$ans = explode(" ",$item->$fieldname);
					foreach ($ans as $i) {
						$resultsemail .= $optionsdata[$i].'<br />';
					}
					$resultsemail .= '<br />';
				} else if ($d->q_type != "captcha") {
					$resultsemail .= $item->$fieldname.'<br />';
				}
			}
		}
		$resultsemail .= "<br /><br /><b>User Agent:</b> ".$_SERVER['HTTP_USER_AGENT'];
		$resultsemail .= "<br /><b>IP:</b> ".$_SERVER['REMOTE_ADDR'];
		$emllist = Array();
		$emllist = explode(",",$pdata->poll_emailto);

		$replyTo = null;
		if ($pdata->poll_emailreplyto) {
			$replyToField = 'q_'.$pdata->poll_emailreplyto;
			if ($item->$replyToField) $replyTo = $item->$replyToField;
		}

		// Email Results by Options
		if (count($pdata->poll_results_emails)) {
			foreach ($pdata->poll_results_emails as $re) {
				$option = explode("_",$re['option']);
				$question = 'q_'.$option[0];
				$answer = $option[1];
				if ($item->$question == $answer) {
					$emllist = Array();
					$emllist = explode(",",$re['emailto']);
					$mail = JFactory::getMailer();
					$sent = $mail->sendMail( $jconfig->get( 'mailfrom' ), $jconfig->get( 'fromname' ), $emllist, $re['subject'], $resultsemail, true, null, null, null, $replyTo );
				}
			}
		}

		$mail = JFactory::getMailer();
		$sent = $mail->sendMail ($jconfig->get( 'mailfrom' ), $jconfig->get( 'fromname' ), $emllist, $pdata->poll_emailsubject, $resultsemail, true, null, null, null, $replyTo);
	}

	// Confirmation email
	if ($pdata->poll_confemail && $pdata->poll_confemail_to) {
		$requery=$db->getQuery(true);
		$requery->select('*');
		$requery->from('#__mpoll_questions');
		$requery->where('published > 0');
		$requery->where('q_poll = '.$pollid);
		$requery->where('q_type IN ("mcbox","mlist","email","dropdown","multi","cbox","textbox","textar")');
		$requery->order('ordering ASC');
		$db->setQuery( $requery );
		$flist = $db->loadObjectList();
		$completedid = base64_encode('cmplid='.$subid.'&id=' . $pubid);
		$cmplurl = $resultslink.'&cmpl=' . $completedid;
		$payurl = $paylink. '&payment=' . $completedid;

		$confemail = $pdata->poll_confmsg;
		$confemail = str_replace("{name}",$user->name,$confemail);
		$confemail = str_replace("{username}",$user->username,$confemail);
		$confemail = str_replace("{email}",$user->email,$confemail);
		$confemail = str_replace("{resid}",$subid,$confemail);
		$confemail = str_replace("{resurl}",$cmplurl,$confemail);
		$confemail = str_replace("{payurl}",$payurl,$confemail);
		foreach ($flist as $d) {
			$fieldname = 'q_'.$d->q_id;
			if ($d->q_type=="attach") {

			} else if (in_array($fieldname,$optfs)) {
				$youropts="";
				$youropts = $optionsdata[$item->$fieldname];
				if ($other->$fieldname) $youropts .= ': '.$other->$fieldname;
				$confemail = str_replace("{i".$d->q_id."}",$youropts,$confemail);
			} else if (in_array($fieldname,$moptfs)) {
				$youropts="";
				$ans = explode(" ",$item->$fieldname);
				foreach ($ans as $i) {
					$youropts .= $optionsdata[$i].' ';
				}
				$confemail = str_replace("{i".$d->q_id."}",$youropts,$confemail);
			} else {
				$confemail = str_replace("{i".$d->q_id."}",$item->$fieldname,$confemail);
			}
		}
		$confTo = null;
		if ($pdata->poll_confemail_to) {
			$confToField = 'q_'.$pdata->poll_confemail_to;
			if ($item->$confToField) $confTo = $item->$confToField;
		}
		$mail = JFactory::getMailer();
		$sent = $mail->sendMail ($pdata->poll_conffromemail, $pdata->poll_conffromname, $confTo, $pdata->poll_confsubject, $confemail, true);
	}
}
catch (Exception $e)
{
	echo $e->getMessage();

	return false;
}

// Show before results message
if ($pdata->poll_results_msg_before) echo $pdata->poll_results_msg_before;

if ($pdata->poll_payment_enabled) {
	$completedid = base64_encode( 'cmplid=' . $subid . '&id=' . $pubid );
	$payurl = $paylink. '&payment=' . $completedid;
	echo '<p align="center"><a href="'.$payurl.'" class="button uk-button uk-button-default">Pay Here</a></p>';
}

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
	$completedid = base64_encode( 'cmplid=' . $subid . '&id=' . $pubid );
	$cmplurl = $resultslink.'&cmpl=' . $completedid;
	echo '<p align="center"><a href="'.$cmplurl.'" class="button uk-button uk-button-default">Results</a></p>';
}

function canUpload($file,&$err)
{
	$params = JComponentHelper::getParams('com_media');

	//Check for File
	if (empty($file['name'])) {
		$err="No File";
		return false;
	}

	//Check filename is safe
	jimport('joomla.filesystem.file');
	if ($file['name'] !== JFile::makesafe($file['name'])) {
		$err="Bad file name";
		return false;
	}

	$format = strtolower(JFile::getExt($file['name']));

	//Check if type allowed
	$allowable = explode(',', $params->get('upload_extensions'));
	$ignored = explode(',', $params->get('ignore_extensions'));
	if (!in_array($format, $allowable) && !in_array($format, $ignored))
	{
		$err="Filetype Not Allowed";
		return false;
	}

	//Check for size
	$maxSize = (int) ($params->get('upload_maxsize', 0) * 1024 * 1024);
	if ($maxSize > 0 && (int) $file['size'] > $maxSize)
	{
		$err = 'File too Large';
		return false;
	}


	//other checks
	$xss_check =  JFile::read($file['tmp_name'], false, 256);
	$html_tags = array('abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--');
	foreach($html_tags as $tag) {
		// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
		if (stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>')) {
			$err="Bad file";
			return false;
		}
	}
	return true;
}