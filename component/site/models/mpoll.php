<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

// Load Extra Pakcages
require JPATH_COMPONENT."/vendor/autoload.php";

use MReCaptcha\MReCaptcha;

class MPollModelMPoll extends JModelLegacy
{
	var $errmsg = "";

	function getPoll($pollid)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_polls');
		$query->where('poll_id = '.$db->escape($pollid));
		$query->where('published > 0');
		$db->setQuery( $query );
		$pdata = $db->loadObject();

		if ($pdata) {
			if ( property_exists( $pdata, 'poll_results_emails' ) && $pdata->poll_results_emails !== null ) {
				$registry = new Registry;
				$registry->loadString( $pdata->poll_results_emails );
				$pdata->poll_results_emails = $registry->toArray();
			}
		}

		return $pdata;
	}

	function getQuestions($pollid,$options=false,$count=false)
	{
		$db = JFactory::getDBO();
		$app=Jfactory::getApplication();
		$jinput = JFactory::getApplication()->input;
		$query=$db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_questions');
		$query->where('published > 0');
		$query->where('q_poll = '.$db->escape($pollid));
		$query->order('ordering ASC');
		$db->setQuery( $query );
		$qdata = $db->loadObjectList();
		foreach ($qdata as &$q) {

			//Load option and count if needed
			if ($options) {
				//Load options
				if ($q->q_type == "multi" || $q->q_type == "mcbox" || $q->q_type == "dropdown" || $q->q_type == "mlist") {
					$qo=$db->getQuery(true);
					$qo->select('opt_txt as text, opt_id as value, opt_disabled, opt_correct, opt_color, opt_other, opt_selectable');
					$qo->from('#__mpoll_questions_opts');
					$qo->where('opt_qid = '.$q->q_id);
					$qo->where('published > 0');
					$qo->order('ordering ASC');
					$db->setQuery($qo);
					$q->options = $db->loadObjectList();

					//Load counts
					if ($count) {
						foreach ($q->options as &$o) {
							$qa = $db->getQuery(true);
							$qa->select('count(*)');
							$qa->from('#__mpoll_results');
							$qa->where('res_qid = '.$q->q_id);
							$qa->where('res_ans LIKE "%'.$o->value.'%"');
							$qa->group('res_qid');
							$db->setQuery($qa);
							$o->anscount = (int)$db->loadResult();
							if (isset($q->anscount)) $q->anscount = $q->anscount + $o->anscount;
							else $q->anscount = $o->anscount;
						}
					}
				}
			}

			//Load Question Params
			$registry = new JRegistry();
			$registry->loadString($q->params);
			$q->params = $registry->toObject();

			//Set default/saved values
			$fn='q_'.$q->q_id;
			$value = $app->getUserState('mpoll.poll'.$pollid.'.'.$fn,null);
			if (!$value) {
				$value = $jinput->getVar($fn,$q->q_default);
			}
			$other = $app->getUserState('mpoll.poll'.$pollid.'.'.$fn.'_other',$q->q_default);
			if ($q->q_type == 'mlimit' || $q->q_type == 'multi' || $q->q_type == 'dropdown' || $q->q_type == 'mcbox' || $q->q_type == 'mlist') {
				$q->value=explode(" ",$value);
				$q->other = $other;
			} else if ($q->q_type == 'cbox' || $q->q_type == 'yesno') {
				$q->value=$value;
			} else if ($q->q_type == 'datedropdown') {
				$q->value=$value;
			} else {
				$q->value=$value;
			}
		}
		return $qdata;
	}

	public function save($pollid)
	{
		$this->checkToken() or die(Text::_('JINVALID_TOKEN'));

		// Initialise variables
		$cfg = MPollHelper::getConfig();
		$jinput = JFactory::getApplication()->input;

		// Honeypot
		if ($cfg->usehoneypot) {
			$honeyPot = $jinput->getVar( 'name', "" );
			if ( $honeyPot ) {
				die( Text::_( 'JINVALID_TOKEN' ) );
			}
		}

		$data		= $jinput->getVar('jform', array(), 'post', 'array');
		$isNew = true;
		$db		= $this->getDbo();
		$app=Jfactory::getApplication();
		$session=JFactory::getSession();
		$user = JFactory::getUser();
		$date = new JDate('now');
		$pollinfo = $this->getPoll($pollid);
		$jconfig = JFactory::getConfig();
		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		if ($pollinfo->poll_regreq == 1 && $user->id == 0) {
			$this->setError('Login required.  Please resubmit.');
			return false;
		} else {
			if ( !in_array($pollinfo->access,$user->getAuthorisedViewLevels())) {
				$this->setError('Access denied');
				return false;
			}
		}

		// Allow an exception to be thrown.
		try
		{
			// Setup item and bind data
			$fids = array();
			$optfs = array();
			$moptfs = array();
			$upfile=array();
			$item = new stdClass();
			$other = new stdClass();
			$flist = $this->getQuestions($pollid,false);
			foreach ($flist as $d) {
				$fieldname = 'q_'.$d->q_id;
				if ($d->q_type == 'attach') {
					$upfile[]=$fieldname;
				} else if ($d->q_type=="mcbox" || $d->q_type=="mlist") {
					$item->$fieldname = implode(" ",$data[$fieldname]);
				} else if ($d->q_type=='cbox') {
					$item->$fieldname = ($data[$fieldname]=='on') ? "1" : "0";
				} else if ($d->q_type=='datedropdown') {
					$fmonth = (int)$data[$fieldname.'_month'];
					$fday = (int)$data[$fieldname.'_day'];
					$fyear = (int)$data[$fieldname.'_year'];
					if ($fmonth < 10) $fmonth = "0".$fmonth;
					if ($fday < 10) $fday = "0".$fday;
					$item->$fieldname = $fmonth.'-'.$fday.'-'.$fyear;
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
				if ($d->cf_type != 'password') {
					$app->setUserState('mpoll.poll'.$pollid.'.'.$fieldname, $item->$fieldname);
					if ($other->$fieldname) {
						$app->setUserState('mpoll.poll'.$pollid.'.'.$fieldname.'_other', $other->$fieldname);
					}
				}
			}

			// reCAPTCHA
			if ($pollinfo->poll_recaptcha) {
				$recaptchaCheck = new MReCaptcha($cfg->rc_api_secret);

				if ($cfg->rc_theme == "v3") {
					$resp = $recaptchaCheck->setScoreThreshold($cfg->rc_threshold)
					                  ->setExpectedAction('submit')
					                  ->verify($_POST["g-recaptcha-response"]);
				} else {
					$resp = $recaptchaCheck->verify($_POST["g-recaptcha-response"]);
				}

				if (!$resp->isSuccess()) {
					$this->setError('reCAPTCHA verification unsuccessful.  Please resubmit.');
					return false;
				}
			}

			// Save completed
			$pubid = bin2hex(random_bytes(16));
			$cmrec=new stdClass();
			$cmrec->cm_user=$user->id;
			$cmrec->cm_poll=$pollid;
			$cmrec->cm_pubid=$pubid;
			if ($pollinfo->poll_payment_enabled) $cmrec->cm_status="unpaid";
			else $cmrec->cm_status="completed";
			$cmrec->cm_useragent=$_SERVER['HTTP_USER_AGENT'];
			$cmrec->cm_ipaddr=$this->getIPAddress();
			if (!$db->insertObject('#__mpoll_completed',$cmrec)) {
				$this->setError("Error saving compleition record.  Please resubmit.");
				return false;
			}
			$subid = $db->insertid();


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
						if ( $this->canUpload( $uf, $err ) ) {
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

			// Save results
			$flist = $this->getQuestions($pollid,false);
			foreach ($flist as $fl) {
				$fieldname = 'q_'.$fl->q_id;
				$cmres=new stdClass();
				$cmres->res_user=$user->id;
				$cmres->res_poll=$pollid;
				$cmres->res_qid=$fl->q_id;
				$cmres->res_ans=$db->escape($item->$fieldname);
				$cmres->res_cm=$subid;
				$cmres->res_ans_other=$db->escape($other->$fieldname);
				if (!$db->insertObject('#__mpoll_results',$cmres)) {
					$this->setError("Error saving additional information.  Please resubmit.");
					return false;
				}
			}

			// Results email
			if ($pollinfo->poll_resultsemail) {
				$flist = $this->getQuestions($pollid,false);
				$resultsemail = 'A submission has been made to the form <strong>'.$pollinfo->poll_name.'</strong> with  ID #<strong>'.$subid.'</strong> Submission data is below.<br /><br />';
				foreach ($flist as $d) {
					if ($d->q_type != "message" && $d->q_type != "header") {
						$fieldname = 'q_'.$d->q_id;
						$resultsemail .= "<b>".$d->q_text.'</b><br />';
						if ($d->q_type=="attach") {
							if($item->$fieldname) {
								$uploaded_files = explode(",",$item->$fieldname);
								foreach ($uploaded_files as $uf) {
									$resultsemail .= 'Download: <a href="' . JURI::base() . $uf . '">'.basename($uf).'</a><br>';
								}
							}
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
                            if ($other->$fieldname) $resultsemail .= 'Other: '.$other->$fieldname;
                            $resultsemail .= '<br />';
							$resultsemail .= '<br />';
						} else {
							$resultsemail .= $item->$fieldname.'<br />';
						}
					}
				}
				$resultsemail .= "<br /><br /><b>User Agent:</b> ".$_SERVER['HTTP_USER_AGENT'];
				$resultsemail .= "<br /><b>IP:</b> ".$this->getIPAddress();

				$replyTo = null;
				if ($pollinfo->poll_emailreplyto) {
					$replyToField = 'q_'.$pollinfo->poll_emailreplyto;
					if ($item->$replyToField) $replyTo = $item->$replyToField;
				}


				// Email Results by Options
				if (count($pollinfo->poll_results_emails)) {
					foreach ($pollinfo->poll_results_emails as $re) {
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

				// All Results email
				if ($pollinfo->poll_emailto) {
					$emllist = Array();
					$emllist = explode(",",$pollinfo->poll_emailto);
					$mail = JFactory::getMailer();
					$sent = $mail->sendMail( $jconfig->get( 'mailfrom' ), $jconfig->get( 'fromname' ), $emllist, $pollinfo->poll_emailsubject, $resultsemail, true, null, null, null, $replyTo );
				}
			}

			// Confirmation email
			if ($pollinfo->poll_confemail && $pollinfo->poll_confemail_to) {
				$flist = $this->getQuestions($pollid,false);
				$completedid = base64_encode('cmplid='.$subid.'&id=' . $pubid);
				$cmplurl = JRoute::_(JUri::root().'index.php?option=com_mpoll&task=results&poll='.$pollinfo->poll_id. '&cmpl=' . $completedid,false);
				$payurl = JRoute::_(JUri::root().'index.php?option=com_mpoll&task=pay&poll='.$pollinfo->poll_id. '&payment=' . $completedid,false);

				$confemail = $pollinfo->poll_confmsg;
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
				if ($pollinfo->poll_confemail_to) {
					$confToField = 'q_'.$pollinfo->poll_confemail_to;
					if ($item->$confToField) $confTo = $item->$confToField;
				}
				$mail = &JFactory::getMailer();
				$sent = $mail->sendMail ($pollinfo->poll_conffromemail, $pollinfo->poll_conffromname, $confTo, $pollinfo->poll_confsubject, $confemail, true);
			}

		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $subid;
	}

	function getCasted($pollid) {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		if (!$user->id) return false;

		$query=$db->getQuery(true);
		$query->select('cm_id');
		$query->from('#__mpoll_completed');
		$query->where('cm_user='.$user->id);
		$query->where('cm_poll='.$db->escape($pollid));
		$db->setQuery($query);
		$data = $db->loadColumn();

		if (count($data)) return true;
		else return false;
	}

	function getCompletition($cmid,$pubid = null) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('*');
		$q->from('#__mpoll_completed');
		$q->where('cm_id = '.$db->escape($cmid));
		if ($pubid) $q->where('cm_pubid = "'.$pubid.'"');
		$q->setLimit('1');
		$db->setQuery($q);
		$data = $db->loadObject();
		return $data;
	}
	function getFirstCast($pollid) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('cm_time');
		$q->from('#__mpoll_completed');
		$q->where('cm_poll = '.$db->escape($pollid));
		$q->order('cm_time ASC');
		$q->setLimit('1');
		$db->setQuery($q);
		$data = $db->loadAssoc();
		return $data['cm_time'];
	}
	function getLastCast($pollid) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('cm_time');
		$q->from('#__mpoll_completed');
		$q->where('cm_poll = '.$db->escape($pollid));
		$q->order('cm_time DESC');
		$q->setLimit('1');
		$db->setQuery($q);
		$data = $db->loadAssoc();
		return $data['cm_time'];
	}
	function getNumCast($pollid) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('count(*)');
		$q->from('#__mpoll_completed');
		$q->where('cm_poll = '.$db->escape($pollid));
		$q->group('cm_poll');
		$db->setQuery($q);
		$count = $db->loadResult();
		return (int)$count;
	}

	function applyAnswers($qdata,$cmplid) {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		foreach ($qdata as &$q) {
			$qa = $db->getQuery(true);
			$qa->select('res_ans');
			$qa->from('#__mpoll_results');
			$qa->where('res_user='.$user->id);
			$qa->where('res_qid='.$q->q_id);
			$qa->where('res_cm='.$cmplid);
			$db->setQuery($qa);
			$q->answer=$db->loadResult();
		}
		return $qdata;
	}

	public static function canUpload($file,&$err)
	{
		$params = JComponentHelper::getParams('com_media');

		//Check for File
		if (empty($file['name'])) {
			$err="No File.  Please resubmit.";
			return false;
		}

		//Check filename is safe
		jimport('joomla.filesystem.file');
		if ($file['name'] !== JFile::makesafe($file['name'])) {
			$err="Bad file name. Please resubmit.";
			return false;
		}

		$format = strtolower(JFile::getExt($file['name']));

		//Check if type allowed
		if (JVersion::MAJOR_VERSION == 3) $allowable = explode(',', $params->get('upload_extensions'));
		else $allowable = explode(',', $params->get('restrict_uploads_extensions'));
		$ignored = explode(',', $params->get('ignore_extensions'));
		if (!in_array($format, $allowable) && !in_array($format, $ignored))
		{
			$err="Filetype Not Allowed.  Please resubmit.";
			return false;
		}

		//Check for size
		$maxSize = (int) ($params->get('upload_maxsize', 0) * 1024 * 1024);
		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$err = 'File too Large.  Please resubmit.';
			return false;
		}


		//other checks
		$xss_check =  file_get_contents($file['tmp_name'], false, null, -1, 256);
		$html_tags = array('abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend', 'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--');
		foreach($html_tags as $tag) {
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (stristr($xss_check, '<'.$tag.' ') || stristr($xss_check, '<'.$tag.'>')) {
				$err="Bad file.  Please resubmit.";
				return false;
			}
		}
		return true;
	}

	public function PayPalCreate($poll,$completition) {

		$cfg = MPollHelper::getConfig();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		$cost = $poll->poll_payment_amount;

		$newpayment=new stdClass();
		$newpayment->pay_cm=$completition->cm_id;
		$newpayment->pay_poll=$poll->poll_id;
		$newpayment->pay_type='paypal';
		$newpayment->pay_status='started';
		$newpayment->pay_amount=floatval($cost);
		$newpayment->pay_updated=date("Y-m-d H:i:s");
		if (!$db->insertObject('#__mpoll_payment',$newpayment)) {
			$this->setError("Error creating payment");
			return false;
		}
		$newpaymentid = $db->insertid();

		$invoicenum = (1000+$poll->poll_id).'-'.(1000000+$completition->cm_id).'-'.str_pad($newpaymentid,8,0,STR_PAD_LEFT);
		$setinvoice = $db->getQuery(true);
		$setinvoice->update('#__mpoll_payment');
		$setinvoice->set('pay_invoice = "'.$db->escape($invoicenum).'"');
		$setinvoice->where('pay_id = '.$newpaymentid);
		$db->setQuery($setinvoice);
		$db->query();


		$paypal = $this->getPayPalApiContext();

		$payer = new \PayPal\Api\Payer();
		$payer->setPaymentMethod("paypal");

		$amount = new \PayPal\Api\Amount();
		$amount->setCurrency("USD")->setTotal($cost);

		$transaction = new \PayPal\Api\Transaction();
		$transaction->setAmount($amount)->setDescription($poll->poll_payment_title)->setInvoiceNumber($invoicenum);

		$redirectUrls = new \PayPal\Api\RedirectUrls();
		$redirectUrls->setReturnUrl(JRoute::_( JUri::root().'index.php?option=com_mpoll&task=pay&poll='.$poll->poll_id.'&payment=' . base64_encode('cmplid='.$completition->cm_id.'&id=' . $completition->cm_pubid),false,true ));
		$redirectUrls->setCancelUrl(JRoute::_( JUri::root().'index.php?option=com_mpoll&task=paypal_cancel_link&poll='.$poll->poll_id.'&payment=' . base64_encode('cmplid='.$completition->cm_id.'&id=' . $completition->cm_pubid),false,true ));

		$ppPayment = new \PayPal\Api\Payment();
		$ppPayment->setIntent("sale")->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions(array($transaction));

		try {
			$ppPayment->create($paypal);
		} catch (\PayPal\Exception\PayPalConnectionException $ex) {
			$setstatus = $db->getQuery(true);
			$setstatus->update('#__mpoll_payment');
			$setstatus->set('pay_status = "error"');
			$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
			$setstatus->where('pay_id = '.$newpaymentid);
			$db->setQuery($setstatus);
			$db->query();
			$setcmstatus = $db->getQuery(true);
			$setcmstatus->update('#__mpoll_completed');
			$setcmstatus->set('cm_status = "error"');
			$setcmstatus->where('cm_id = '.$payment->pay_cm);
			$db->setQuery($setcmstatus);
			$db->query();
			$this->AddPaymentLog($newpaymentid,null,$ex->getData());
			$this->setError("PayPal Error");
			return false;
		}

		$this->AddPaymentLog($newpaymentid,null,$ppPayment->toJSON());

		$setstatus = $db->getQuery(true);
		$setstatus->update('#__mpoll_payment');
		$setstatus->set('pay_status = "created"');
		$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
		$setstatus->where('pay_id = '.$newpaymentid);
		$db->setQuery($setstatus);
		$db->query();

		return json_encode(array('id' => $ppPayment->getId()));
	}

	public function PayPalExecute($poll,$completition) {

		$cfg = MPollHelper::getConfig();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		$jinput = JFactory::getApplication()->input;

		$paymentId = $jinput->getVar('paymentID');
		$payerId = $jinput->getVar('payerID');

		if (!$payerId || !$paymentId) {
			$this->setError("Payment ID or Payer ID Not provided");
			return false;
		}

		$paypal = $this->getPayPalApiContext();

		try {
			$ppPayment = \PayPal\Api\Payment::get($paymentId, $paypal);
		} catch (Exception $ex) {
			$this->setError($ex->getData());
			return false;
		}

		$paymentquery = $db->getQuery(true);
		$paymentquery->select('*');
		$paymentquery->from('#__mpoll_payment');
		$paymentquery->where('pay_invoice = "'.$db->escape($ppPayment->transactions[0]->invoice_number).'"');
		$paymentquery->where('pay_cm = '.$completition->cm_id);
		$db->setQuery( $paymentquery );
		$payment = $db->loadObject();
		if (!$payment) {
			$this->setError("Could not find Payment");
			return false;
		}

		$execution = new \PayPal\Api\PaymentExecution();
		$execution->setPayerId($payerId);

		try {
			// Execute the payment
			// (See bootstrap.php for more on `ApiContext`)
			$result = $ppPayment->execute($execution, $paypal);

			try {
				$ppPayment = \PayPal\Api\Payment::get($paymentId, $paypal);
			} catch (\PayPal\Exception\PayPalConnectionException $ex) {
				$setstatus = $db->getQuery(true);
				$setstatus->update('#__mpoll_payment');
				$setstatus->set('pay_status = "get_error"');
				$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
				$setstatus->where('pay_id = '.$payment->pay_id);
				$db->setQuery($setstatus);
				$db->query();
				$setcmstatus = $db->getQuery(true);
				$setcmstatus->update('#__mpoll_completed');
				$setcmstatus->set('cm_status = "error"');
				$setcmstatus->where('cm_id = '.$payment->pay_cm);
				$db->setQuery($setcmstatus);
				$db->query();
				$this->AddPaymentLog($payment->pay_id,null,$ex->getData());
				$this->setError("PayPal Error");
				return false;
			}
		} catch (\PayPal\Exception\PayPalConnectionException $ex) {
			$setstatus = $db->getQuery(true);
			$setstatus->update('#__mpoll_payment');
			$setstatus->set('pay_status = "execute_error"');
			$setstatus->set('pay_id = '.$payment->pay_id);
			$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
			$db->setQuery($setstatus);
			$db->query();
			$setcmstatus = $db->getQuery(true);
			$setcmstatus->update('#__mpoll_completed');
			$setcmstatus->set('cm_status = "error"');
			$setcmstatus->where('cm_id = '.$payment->pay_cm);
			$db->setQuery($setcmstatus);
			$db->query();
			$this->AddPaymentLog($payment->pay_id,null,$ex->getData());
			$this->setError("PayPal Error");
			return false;
		}

		$setstatus = $db->getQuery(true);
		$setstatus->update('#__mpoll_payment');
		$setstatus->set('pay_status = "'.$db->escape($ppPayment->getState()).'"');
		$setstatus->set('pay_trans = "'.$db->escape($ppPayment->getTransactions()[0]->related_resources[0]->sale->id).'"');
		$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
		$setstatus->where('pay_id = '.$payment->pay_id);
		$db->setQuery($setstatus);
		$db->query();

		if ($ppPayment->state == 'approved') {
			$setcmstatus = $db->getQuery(true);
			$setcmstatus->update('#__mpoll_completed');
			$setcmstatus->set('cm_status = "approved"');
			$setcmstatus->where('cm_id = '.$payment->pay_cm);
			$db->setQuery($setcmstatus);
			$db->query();

			// User Notification Email
			if ($poll->poll_payment_to) {
				$qa = $db->getQuery(true);
				$qa->select('res_ans');
				$qa->from('#__mpoll_results');
				$qa->where('res_qid='.$poll->poll_payment_to);
				$qa->where('res_cm='.$payment->pay_cm);
				$db->setQuery($qa);
				$toEmail=$db->loadResult();

				if ($toEmail) {
					$mail = &JFactory::getMailer();
					$sent = $mail->sendMail( $poll->poll_payment_fromemail, $poll->poll_payment_fromname, $toEmail, $poll->poll_payment_subject, $poll->poll_payment_body, true );
				}
			}

			// Admin Notification Email
			if ($poll->poll_payment_adminemail) {
				$resultsemail = 'A payment has been made to the form <strong>'.$poll->poll_name.'</strong> with  ID #<strong>'.$payment->pay_cm.'</strong>';

				$emllist = Array();
				$emllist = explode(",",$poll->poll_payment_adminemail);

				$replyTo = null;
				if ($poll->poll_payment_to) {
					$replyToField = 'q_'.$poll->poll_payment_to;
					if ($item->$replyToField) $replyTo = $item->$replyToField;
				}

				$mail = &JFactory::getMailer();
				$sent = $mail->sendMail ($poll->poll_payment_fromemail, $poll->poll_payment_fromname, $emllist, $poll->poll_payment_adminsubject, $resultsemail, true, null, null, null, $replyTo);
			}

		}

		$this->AddPaymentLog($payment->pay_id,null,$ppPayment->toJSON());

		return json_encode(array('state' => $ppPayment->getState()));
	}

	public function PayPalCancel($poll,$completition) {
		$cfg = MPollHelper::getConfig();
		$db   = JFactory::getDBO();
		$user = JFactory::getUser();
		$jinput = JFactory::getApplication()->input;

		$paymentId = $jinput->getVar('paymentID');

		$paymentquery = $db->getQuery(true);
		$paymentquery->select('*');
		$paymentquery->from('#__mpoll_payment');
		$paymentquery->where('pay_cm = '.$completition->cm_id);
		$paymentquery->where("pay_status IN ('started','created')");
		$paymentquery->order('pay_created DESC');
		$db->setQuery( $paymentquery );
		$payment = $db->loadObject();
		if (!$payment) {
			$this->setError("Could not find Payment");
			return false;
		}

		$setstatus = $db->getQuery(true);
		$setstatus->update('#__mpoll_payment');
		$setstatus->set('pay_status = "cancelled"');
		$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
		$setstatus->where('pay_id = '.$payment->pay_id);
		$db->setQuery($setstatus);
		$db->query();

		$setcmstatus = $db->getQuery(true);
		$setcmstatus->update('#__mpoll_completed');
		$setcmstatus->set('cm_status = "cancelled"');
		$setcmstatus->where('cm_id = '.$payment->pay_cm);
		$db->setQuery($setcmstatus);
		$db->query();

		return json_encode(array('id'=>$payment->pay_id));
	}

	public function PayPalWebhook() {
		$cfg = MPollHelper::getConfig();
		$db   = JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;
		$jsondata = $jinput->json->getRaw();
		$headers = array_change_key_case($this->getHeaders(), CASE_UPPER);

		if ($jsondata) {
			$content = json_decode($jsondata,true);
			$whid = $content['id'];
			$invoice = $content['resource']['invoice_number'];
			$state = $content['resource']['state'];

			if ($whid) {

				$signatureVerification = new \PayPal\Api\VerifyWebhookSignature();
				$signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
				$signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
				$signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
				$signatureVerification->setWebhookId($cfg->paypal_api_webhook);
				$signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
				$signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);

				$signatureVerification->setRequestBody($jsondata);

				$output = $signatureVerification->post($this->getPayPalApiContext());

				if ($output->verification_status == 'SUCCESS') {

					$paymentquery = $db->getQuery(true);
					$paymentquery->select('*');
					$paymentquery->from('#__mpoll_payment');
					$paymentquery->where('pay_invoice = "'.$db->escape($invoice).'"');
					$db->setQuery( $paymentquery );
					$payment = $db->loadObject();

					if ($payment) {

						$pollquery = $db->getQuery(true);
						$pollquery->select('*');
						$pollquery->from('#__mpoll_polls');
						$pollquery->where('poll_id = '.$payment->pay_poll);
						$db->setQuery( $pollquery );
						$poll = $db->loadObject();

						if ( $content['event_type'] == "PAYMENT.SALE.COMPLETED" ) {

							$setstatus = $db->getQuery(true);
							$setstatus->update('#__mpoll_payment');
							$setstatus->set('pay_status = "'.$db->escape($state).'"');
							$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
							$setstatus->where('pay_id = '.$payment->pay_id);
							$db->setQuery($setstatus);
							$db->query();

							$setcertstatus = $db->getQuery(true);
							$setcertstatus->update('#__mpoll_completed');
							$setcertstatus->set('cm_status = "paid"');
							$setcertstatus->where('cm_id = '.$payment->pay_cm);
							$db->setQuery($setcertstatus);
							$db->query();
						}

						if ( $content['event_type'] == "PAYMENT.SALE.DENIED" ) {

							$setstatus = $db->getQuery(true);
							$setstatus->update('#__mpoll_payment');
							$setstatus->set('pay_status = "'.$db->escape($state).'"');
							$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
							$setstatus->where('pay_id = '.$payment->pay_id);
							$db->setQuery($setstatus);
							$db->query();

							$setcertstatus = $db->getQuery(true);
							$setcertstatus->update('#__mpoll_completed');
							$setcertstatus->set('cm_status = "failed"');
							$setcertstatus->where('cm_id = '.$payment->pay_cm);
							$db->setQuery($setcertstatus);
							$db->query();

							// Admin Notification Email
							if ($poll->poll_payment_adminemail) {
								$resultsemail = 'A payment has failed for the form <strong>'.$poll->poll_name.'</strong> with submission ID #<strong>'.$payment->pay_cm.'</strong>';

								$emllist = Array();
								$emllist = explode(",",$poll->poll_payment_adminemail);

								$replyTo = null;
								if ($poll->poll_payment_to) {
									$replyToField = 'q_'.$poll->poll_payment_to;
									if ($item->$replyToField) $replyTo = $item->$replyToField;
								}

								$mail = &JFactory::getMailer();
								$sent = $mail->sendMail ($poll->poll_payment_fromemail, $poll->poll_payment_fromname, $emllist, $poll->poll_payment_adminsubject, $resultsemail, true, null, null, null, $replyTo);
							}
						}

						if ( $content['event_type'] == "PAYMENT.SALE.REFUNDED" ) {

							$setstatus = $db->getQuery(true);
							$setstatus->update('#__mpoll_payment');
							$setstatus->set('pay_status = "refunded"');
							$setstatus->set('pay_updated = "'.date("Y-m-d H:i:s").'"');
							$setstatus->where('pay_id = '.$payment->pay_id);
							$db->setQuery($setstatus);
							$db->query();

							$setcertstatus = $db->getQuery(true);
							$setcertstatus->update('#__mpoll_completed');
							$setcertstatus->set('cm_status = "refunded"');
							$setcertstatus->where('cm_id = '.$payment->pay_cm);
							$db->setQuery($setcertstatus);
							$db->query();

							// Admin Notification Email
							if ($poll->poll_payment_adminemail) {
								$resultsemail = 'A payment has been refunded for the form <strong>'.$poll->poll_name.'</strong> with submission ID #<strong>'.$payment->pay_cm.'</strong>';

								$emllist = Array();
								$emllist = explode(",",$poll->poll_payment_adminemail);

								$replyTo = null;
								if ($poll->poll_payment_to) {
									$replyToField = 'q_'.$poll->poll_payment_to;
									if ($item->$replyToField) $replyTo = $item->$replyToField;
								}

								$mail = &JFactory::getMailer();
								$sent = $mail->sendMail ($poll->poll_payment_fromemail, $poll->poll_payment_fromname, $emllist, $poll->poll_payment_adminsubject, $resultsemail, true, null, null, null, $replyTo);
							}
						}

						$this->AddPaymentLog($payment->pay_id,json_encode( $headers ),$jsondata);
					}

				}
			}

		}
		return 'true';
	}

	private function getHeaders() {
		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}

	private function getPayPalApiContext() {
		$cfg = MPollHelper::getConfig();

		\PayPal\Core\PayPalHttpConfig::$defaultCurlOptions[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;

		$apiContext = new \PayPal\Rest\ApiContext( new \PayPal\Auth\OAuthTokenCredential( $cfg->paypal_api_id, $cfg->paypal_api_secret ) );

		if ($cfg->paypal_mode == "production") {
			$mode = 'live';
		} else {
			$mode = $cfg->paypal_mode;
		}

		$apiContext->setConfig(
			array(
				'mode' => $mode
			)
		);

		return $apiContext;
	}


	public function AddPaymentLog($payment,$headers=null,$data=null) {
		$db   = JFactory::getDBO();
		$newpaylog=new stdClass();
		$newpaylog->log_payment=$payment;
		if ($headers) $newpaylog->log_headers=$db->escape($headers);
		if ($data) $newpaylog->log_data=$db->escape($data);
		$db->insertObject('#__mpoll_payment_log',$newpaylog);
	}

	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = Session::checkToken($method);

		return $valid;
	}

	public function getIPAddress() {
		//whether ip is from the share internet
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		//whether ip is from the proxy
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		//whether ip is from the remote address
		else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}


}
