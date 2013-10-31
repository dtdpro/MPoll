<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelMPoll extends JModelLegacy
{
	var $errmsg = "";
	
	function getPoll($pollid)
	{
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$query = 'SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid.' && published > 0';
		$db->setQuery( $query );
		$pdata = $db->loadObject();
		return $pdata;
	}
	function getQuestions($pollid,$options=false)
	{
		$db =& JFactory::getDBO();
		$app=Jfactory::getApplication();
		$query = 'SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE published > 0 && q_poll = '.$pollid.' ORDER BY ordering ASC';
		$db->setQuery( $query );
		$qdata = $db->loadObjectList();
		if ($options) {
			foreach ($qdata as &$q) {
				if ($q->q_type == "multi" || $q->q_type == "mcbox" || $q->q_type == "dropdown" || $q->q_type == "mlist") {
					$qo="SELECT opt_txt as text, opt_id as value, opt_disabled, opt_correct, opt_color, opt_other, opt_selectable FROM #__mpoll_questions_opts WHERE opt_qid = ".$q->q_id." && published > 0 ORDER BY ordering ASC";
					$db->setQuery($qo);
					$q->options = $db->loadObjectList();
				}
			}
		}
		foreach ($qdata as &$u) {
			$fn='q_'.$u->q_id;
			$value = $app->getUserState('mpoll.poll'.$pollid.'.'.$fn,'');
			$other = $app->getUserState('mpoll.poll'.$pollid.'.'.$fn.'_other','');
			if(!$value && $udata->$fn) $value = $udata->$fn;
			else if (!$value) $value=$u->q_default;
			if ($u->q_type == 'mlimit' || $u->q_type == 'multi' || $u->q_type == 'dropdown' || $u->q_type == 'mcbox' || $u->q_type == 'mlist') {
				$u->value=explode(" ",$value); 
				$u->other = $other;
			} else if ($u->q_type == 'cbox' || $u->q_type == 'yesno') {
				$u->value=$value;
			} else if ($u->q_type == 'birthday') {
				$u->value=$value;
			} else if ($u->q_type != 'captcha') {
				$u->value=$value;
			}
		}
		return $qdata;
	}
	
	public function save($pollid)
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		// Initialise variables;
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
			//setup item and bind data
			$fids = array();
			$optfs = array();
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
				} else if ($d->q_type=='birthday') {
					$fmonth = (int)$data[$fieldname.'_month'];
					$fday = (int)$data[$fieldname.'_day'];
					if ($fmonth < 10) $fmonth = "0".$fmonth;
					if ($fday < 10) $fday = "0".$fday;
					$item->$fieldname = $fmonth.$fday;
				} else {
					$item->$fieldname = $data[$fieldname];
				}
				if ($d->q_type=="mcbox" || $d->q_type=="mlist" || $d->q_type=="multi" || $d->q_type=="dropdown") { 
					$optfs[]=$fieldname;
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
			$item->site_url = JURI::base();
			
			//Check CAPTCHA
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
			
			//save completed
			$qc = 'INSERT INTO #__mpoll_completed (cm_user,cm_poll) VALUES ('.$user->id.','.$pollid.')';
			$db->setQuery( $qc );
			$db->query();
			$subid = $db->insertid();
			
			//Upload
			foreach ($upfile as $u) {
				$userfile = JRequest::getVar($u, null, 'files', 'array');
				if (!empty($userfile['name'])) {
					// Build the appropriate paths
					$config		= JFactory::getConfig();
					$tmp_dest	= JPATH_BASE.'/media/com_mpoll/upload/' . $subid."_".str_replace("q_","",$u)."_".$userfile['name'];
					$tmp_src	= $userfile['tmp_name'];
				
					// Move uploaded file
					jimport('joomla.filesystem.file');
					if ($this->canUpload($userfile,$err)) {
						$uploaded = JFile::upload($tmp_src, $tmp_dest);
						$item->$u = '/media/com_mpoll/upload/' . $subid."_".str_replace("q_","",$u)."_".$userfile['name'];
					} else {
						$this->setError($err);
						return false;
					}
				} else { $item->$u = ""; }
			}
	
			$odsql = "SELECT * FROM #__mpoll_questions_opts";
			$db->setQuery($odsql);
			$optionsdata = array();
			$optres = $db->loadObjectList();
			foreach ($optres as $o) {
				$optionsdata[$o->opt_id]=$o->opt_txt;
			}
			
			// Results email
			if ($pollinfo->poll_resultsemail) {
				$flist = $this->getQuestions($pollid,false);
				$resultsemail = "";
				foreach ($flist as $d) {
					if ($d->q_type != "captcha") {
						$fieldname = 'q_'.$d->q_id;
						$resultsemail .= "<b>".$d->q_text.'</b><br />';
					}
					if ($d->q_type=="attach") {
						if($item->$fieldname) $resultsemail .= '<a href="'.JURI::base(  ).$item->$fieldname.'">Download</a>';
					} else if (in_array($fieldname,$optfs)) {
						if ($d->q_type == "mcbox" || $d->q_type=="mlist") { 
							$ans = explode(" ",$item->$fieldname);
							foreach ($ans as $i) {
								$resultsemail .= $optionsdata[$i].'<br />';
							}
						} else {
							$resultsemail .= $optionsdata[$item->$fieldname];
							if ($other->$fieldname) $resultsemail .= ': '.$other->$fieldname;
							$resultsemail .= '<br />';
						}
						$resultsemail .= '<br />';
					} else if ($d->q_type != "captcha") {
						$resultsemail .= $item->$fieldname.'<br />';
					}
				}
				$emllist = Array();
				$emllist = explode(",",$pollinfo->poll_emailto);
				
				$mail = &JFactory::getMailer();
				$sent = $mail->sendMail ($jconfig->get( 'config.mailfrom' ), $jconfig->get( 'config.fromname' ), $emllist, $pollinfo->poll_emailsubject, $resultsemail, true);
			}
			
			//confirmation email
			if ($pollinfo->poll_confemail && $user->id) {
				$flist = $this->getQuestions($pollid,false);
				$confemail = $pollinfo->poll_confmsg;
				$confemail = str_replace("{name}",$user->name,$confemail);
				$confemail = str_replace("{username}",$user->username,$confemail);
				$confemail = str_replace("{email}",$user->email,$confemail);
				foreach ($flist as $d) {
					$fieldname = 'q_'.$d->q_id;
					if ($d->q_type=="attach") {
						
					} else if (in_array($fieldname,$optfs)) {
						$youropts="";
						if ($d->q_type == "mcbox" || $d->q_type=="mlist") {
							$ans = explode(" ",$item->$fieldname);
							foreach ($ans as $i) {
								$youropts .= $optionsdata[$i].' ';
							}
						} else {
							$youropts = $optionsdata[$item->$fieldname];
							if ($other->$fieldname) $youropts .= ': '.$other->$fieldname;
						}
						$confemail = str_replace("{i".$d->q_id."}",$youropts,$confemail);
					} else if ($d->q_type != "captcha") {
						$confemail = str_replace("{i".$d->q_id."}",$item->$fieldname,$confemail);
					}
				}
				$mail = &JFactory::getMailer();
				$sent = $mail->sendMail ($pollinfo->poll_conffromemail, $pollinfo->poll_conffromname, $user->email, $pollinfo->poll_confsubject, $confemail, true);
			}
			
			// Save poll info
			$flist = $this->getQuestions($pollid,false);
			foreach ($flist as $fl) {
				$fieldname = 'q_'.$fl->q_id;
				if ($fl->q_type != "captcha") {
					$q = 'INSERT INTO #__mpoll_results	(res_user,res_poll,res_qid,res_ans,res_cm,res_ans_other) VALUES ("'.$user->id.'","'.$pollid.'","'.$fl->q_id.'","'.$db->escape($item->$fieldname).'","'.$subid.'","'.$db->escape($other->$fieldname).'")';
					$db->setQuery( $q );
					if (!$db->query()) {
						$this->setError("Error saving additional information");
						return false;
					}
				}
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
		$db =& JFactory::getDBO();
		//$sewn = JFactory::getSession();
		//$sessionid = $sewn->getId();
		$user =& JFactory::getUser();
		$userid = $user->id;
		$query = 'SELECT * FROM #__mpoll_completed WHERE cm_user="'.$userid.'" && cm_poll="'.$pollid.'"';
		$db->setQuery($query);
		$data = $db->loadAssoc();
		if (count($data) > 0) return true;
		else return false;
	}
	
	function getPolls($catid) {
		$query  = ' SELECT * ';
		$query .= ' FROM #__mpoll_polls';
		$query .= ' WHERE published = 1 && poll_cat = '.$catid;
		$query .= ' ORDER BY poll_name ASC';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$data = $db->loadObjectList();
		return $data;
	}
	
	function getFirstCast($pollid) {
		$q = 'SELECT cm_time FROM #__mpoll_completed WHERE cm_poll = '.$pollid.' ORDER BY cm_time ASC LIMIT 1';
		$db =& JFactory::getDBO();
		$db->setQuery($q); 
		$data = $db->loadAssoc(); 
		return $data['cm_time'];
	}
	function getLastCast($pollid) {
		$q = 'SELECT cm_time FROM #__mpoll_completed WHERE cm_poll = '.$pollid.' ORDER BY cm_time DESC LIMIT 1';
		$db =& JFactory::getDBO();
		$db->setQuery($q); 
		$data = $db->loadAssoc(); 
		return $data['cm_time'];
	}
	function getNumCast($pollid) {
		$q = 'SELECT count(*),cm_poll FROM #__mpoll_completed WHERE cm_poll = '.$pollid.' GROUP BY cm_poll';
		$db =& JFactory::getDBO();
		$db->setQuery($q); 
		$data = $db->loadAssoc();
		if ($data) return $data['count(*)'];
		else return 0;
	}
	
	function applyAnswers($qdata,$cmplid) {
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		foreach ($qdata as &$q) {
			$qa = 'SELECT res_ans FROM #__mpoll_results WHERE res_user = '.$user->id.' && res_qid = '.$q->q_id.' && res_cm='.$cmplid;
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
	
		
		//othe checks
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
