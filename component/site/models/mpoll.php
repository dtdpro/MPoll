<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelMPoll extends JModelLegacy
{
	var $errmsg = "";

	function getPoll($pollid)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_polls');
		$query->where('poll_id = '.$pollid);
		$query->where('published > 0');
		$db->setQuery( $query );
		$pdata = $db->loadObject();
		return $pdata;
	}

	function getQuestions($pollid,$options=false,$count=false)
	{
		$db = JFactory::getDBO();
		$app=Jfactory::getApplication();
		$query=$db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_questions');
		$query->where('published > 0');
		$query->where('q_poll = '.$pollid);
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
							$q->anscount = $q->anscount + $o->anscount;
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
			$value = $app->getUserState('mpoll.poll'.$pollid.'.'.$fn,$q->q_default);
			$other = $app->getUserState('mpoll.poll'.$pollid.'.'.$fn.'_other',$q->q_default);
			if ($q->q_type == 'mlimit' || $q->q_type == 'multi' || $q->q_type == 'dropdown' || $q->q_type == 'mcbox' || $q->q_type == 'mlist') {
				$q->value=explode(" ",$value);
				$q->other = $other;
			} else if ($q->q_type == 'cbox' || $q->q_type == 'yesno') {
				$q->value=$value;
			} else if ($q->q_type == 'birthday') {
				$q->value=$value;
			} else if ($q->q_type != 'captcha') {
				$q->value=$value;
			}
		}
		return $qdata;
	}

	public function save($pollid)
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		// Initialise variables;
		$jinput = JFactory::getApplication()->input;
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$dispatcher = JDispatcher::getInstance();
		$isNew = true;
		$db		= $this->getDbo();
		$app=Jfactory::getApplication();
		$session=JFactory::getSession();
		$user = JFactory::getUser();
		$date = new JDate('now');
		$pollinfo = $this->getPoll($pollid);
		$jconfig = JFactory::getConfig();
		$cfg = MPollHelper::getConfig();
		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		if ($pollinfo->poll_regreq == 1 && $user->id == 0) {
			$this->setError('Login required');
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
			$flist = $this->getQuestions($pollid,false);
			foreach ($flist as $d) {
				$fieldname = 'q_'.$d->q_id;
				if ($d->q_type == 'attach') {
					$upfile[]=$fieldname;
				} else if ($d->q_type == 'captcha') {
					$capfield=$fieldname;
				} else if ($d->q_type=="mcbox" || $d->q_type=="mlist") {
					$item->$fieldname = implode(" ",$data[$fieldname]);
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
				if ($d->q_type != 'captcha') $fids[]=$d->uf_id;
				if ($d->cf_type != 'captcha' || $d->cf_type != 'password') {
					$app->setUserState('mpoll.poll'.$pollid.'.'.$fieldname, $item->$fieldname);
					if ($other->$fieldname) {
						$app->setUserState('mpoll.poll'.$pollid.'.'.$fieldname.'_other', $other->$fieldname);
					}
				}
			}

			// reCAPTCHA
			if ($pollinfo->poll_recaptcha) {
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
					$this->setError('reCAPTCHA Response Required');
					return false;
				} else if ($rc_captcha_success->success==true) {

				} else {
					$this->setError('reCAPTCHA Error');
					return false;
				}
			}

			// Check CAPTCHA
			if ($capfield) {
				include_once 'components/com_mpoll/lib/securimage/securimage.php';
				$securimage = new Securimage();
				$securimage->session_name = $session->getName();
				$securimage->case_sensitive  = false;
				if ($securimage->check($data[$capfield]) == false) {
					$this->setError('Security Code Incorrect');
					return false;
				}
			}

			// Save completed
			$cmrec=new stdClass();
			$cmrec->cm_user=$user->id;
			$cmrec->cm_poll=$pollid;
			$cmrec->cm_useragent=$_SERVER['HTTP_USER_AGENT'];
			$cmrec->cm_ipaddr=$_SERVER['REMOTE_ADDR'];
			if (!$db->insertObject('#__mpoll_completed',$cmrec)) {
				$this->setError("Error saving compleition record");
				return false;
			}
			$subid = $db->insertid();


			// Upload files
			foreach ($upfile as $u) {
				$config		= JFactory::getConfig();
				$userfiles = $jinput->files->get($u, array(), 'array'); //JRequest::getVar($u.'[]', array(), 'files', 'array');
				$uploaded_files = array();
				foreach ($userfiles as $uf) {
					// Build the appropriate paths
					$tmp_dest	= JPATH_BASE.'/media/com_mpoll/upload/' . $subid."_".str_replace("q_","",$u)."_".$uf['name'];
					$tmp_src	= $uf['tmp_name'];

					// Move uploaded file
					jimport('joomla.filesystem.file');
					if ($this->canUpload($uf,$err)) {
						$uploaded = JFile::upload($tmp_src, $tmp_dest);
						$uploaded_files[] = '/media/com_mpoll/upload/' . $subid."_".str_replace("q_","",$u)."_".$uf['name'];
					} else {
						$this->setError($err);
						return false;
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
				if ($fl->q_type != "captcha") {
					$cmres=new stdClass();
					$cmres->res_user=$user->id;
					$cmres->res_poll=$pollid;
					$cmres->res_qid=$fl->q_id;
					$cmres->res_ans=$db->escape($item->$fieldname);
					$cmres->res_cm=$subid;
					$cmres->res_ans_other=$db->escape($other->$fieldname);
					if (!$db->insertObject('#__mpoll_results',$cmres)) {
						$this->setError("Error saving additional information");
						return false;
					}
				}
			}

			// Results email
			if ($pollinfo->poll_resultsemail) {
				$flist = $this->getQuestions($pollid,false);
				$resultsemail = "";
				foreach ($flist as $d) {
					if ($d->q_type != "captcha" && $d->q_type != "message" && $d->q_type != "header") {
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
							$resultsemail .= '<br />';
						} else if ($d->q_type != "captcha") {
							$resultsemail .= $item->$fieldname.'<br />';
						}
					}
				}
				$resultsemail .= "<br /><br /><b>User Agent:</b> ".$_SERVER['HTTP_USER_AGENT'];
				$resultsemail .= "<br /><b>IP:</b> ".$_SERVER['REMOTE_ADDR'];
				$emllist = Array();
				$emllist = explode(",",$pollinfo->poll_emailto);

				$mail = &JFactory::getMailer();
				$sent = $mail->sendMail ($jconfig->get( 'mailfrom' ), $jconfig->get( 'fromname' ), $emllist, $pollinfo->poll_emailsubject, $resultsemail, true);
			}

			// Confirmation email
			if ($pollinfo->poll_confemail && $user->id) {
				$flist = $this->getQuestions($pollid,false);
				$confemail = $pollinfo->poll_confmsg;
				$confemail = str_replace("{name}",$user->name,$confemail);
				$confemail = str_replace("{username}",$user->username,$confemail);
				$confemail = str_replace("{email}",$user->email,$confemail);
				$confemail = str_replace("{resid}",$subid,$confemail);
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
					} else if ($d->q_type != "captcha") {
						$confemail = str_replace("{i".$d->q_id."}",$item->$fieldname,$confemail);
					}
				}
				$mail = &JFactory::getMailer();
				$sent = $mail->sendMail ($pollinfo->poll_conffromemail, $pollinfo->poll_conffromname, $user->email, $pollinfo->poll_confsubject, $confemail, true);
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
		$query->where('cm_poll='.$pollid);
		$db->setQuery($query);
		$data = $db->loadColumn();

		if (count($data)) return true;
		else return false;
	}

	function getFirstCast($pollid) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('cm_time');
		$q->from('#__mpoll_completed');
		$q->where('cm_poll = '.$pollid);
		$q->order('cm_time ASC');
		$q->limit('1');
		$db->setQuery($q);
		$data = $db->loadAssoc();
		return $data['cm_time'];
	}
	function getLastCast($pollid) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('cm_time');
		$q->from('#__mpoll_completed');
		$q->where('cm_poll = '.$pollid);
		$q->order('cm_time DESC');
		$q->limit('1');
		$db->setQuery($q);
		$data = $db->loadAssoc();
		return $data['cm_time'];
	}
	function getNumCast($pollid) {
		$db = JFactory::getDBO();
		$q = $db->getQuery(true);
		$q->select('count(*)');
		$q->from('#__mpoll_completed');
		$q->where('cm_poll = '.$pollid);
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


}
