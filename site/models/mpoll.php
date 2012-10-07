<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelMPoll extends JModel
{
	var $errmsg = "";
	
	function getPoll($pollid)
	{
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$query = 'SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid.' && published > 0 && access IN ('.implode(",",$user->getAuthorisedViewLevels()).')';
		$db->setQuery( $query );
		$pdata = $db->loadAssoc();
		return $pdata;
	}
	function getQuestions($courseid)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE published > 0 && q_poll = '.$courseid.' ORDER BY ordering ASC';
		$db->setQuery( $query );
		$qdata = $db->loadObjectList();
		return $qdata;
	}
	function saveBallot($pollid) {
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$userid = $user->id;
		$pollinfo=$this->getPoll($pollid);
		$email = '';
		//save completed
		$qc = 'INSERT INTO #__mpoll_completed (cm_user,cm_poll) VALUES ('.$userid.','.$pollid.')';
		$db->setQuery( $qc );
		$db->query();
		$lastid = $db->insertid();
		//saev answers
		$query = 'SELECT * FROM #__mpoll_questions WHERE published > 0 && q_poll = '.$pollid;
		$db->setQuery( $query );
		$qdata = $db->loadAssocList(); 
		foreach ($qdata as $ques) {
			if ($pollinfo['poll_emailto']) $email .= '<b>'.$ques['q_text'].'</b>';
			$otherans=$db->getEscaped(JRequest::getVar('q'.$ques['q_id'].'o'));
			if ($ques['q_type'] == 'attach') {
				$userfile = JRequest::getVar('q'.$ques['q_id'], null, 'files', 'array');
				if (!empty($userfile['name'])) {
					// Build the appropriate paths
					$config		= JFactory::getConfig();
					$tmp_dest	= JPATH_BASE.'/media/com_mpoll/upload/' . $lastid."_".$ques['q_id']."_".$userfile['name'];
					$tmp_src	= $userfile['tmp_name'];
					
					// Move uploaded file
					jimport('joomla.filesystem.file');
					if ($this->canUpload($userfile,$err)) {
						$uploaded = JFile::upload($tmp_src, $tmp_dest);
						$ans = '/media/com_mpoll/upload/' . $lastid."_".$ques['q_id']."_".$userfile['name'];
					} else {
						$this->errmsg = $err;
						$ans = 'ERROR: '.$err;
					}
				} else { $ans = ""; }
				$q = 'INSERT INTO #__mpoll_results	(res_user,res_poll,res_qid,res_ans,res_cm) VALUES ("'.$userid.'","'.$pollid.'","'.$ques['q_id'].'","'.$ans.'","'.$lastid.'")';
				$db->setQuery( $q );
				$db->query();
				
				if ($pollinfo['poll_emailto']) {
					
					if (strpos($ans,"ERROR:") === FALSE && $ans != "") {
						$anse = '<a href="'.JURI::base(  ).$ans.'">Download</a>';
					} else {
						$anse = $ans;
					}
					$email .= '<br />'.$anse.'<br /><br />';
				}
				
					
				
			} else if ($ques['q_type'] != 'mcbox') {
				$ans = JRequest::getVar('q'.$ques['q_id']);
				if ($pollinfo['poll_emailto']) {
					if ($ques['q_type'] == "multi") {
						$qo = 'SELECT opt_txt FROM #__mpoll_questions_opts WHERE published > 0 && opt_id = '.$ans;
						$db->setQuery($qo); $opt = $db->loadObect();
						if ($opt->opt_other) $result = $otherans;
						else $result = $opt->opt_text;
						$email .= '<br />'.$result.'<br /><br />';
					} else {
						$email .= '<br />'.$ans.'<br /><br />';
					}
				}
				$q = 'INSERT INTO #__mpoll_results	(res_user,res_poll,res_qid,res_ans,res_ans_other,res_cm) VALUES ("'.$userid.'","'.$pollid.'","'.$ques['q_id'].'","'.$ans.'","'.$otherans.'","'.$lastid.'")';
				$db->setQuery( $q );
				$db->query();
			} else {
				$ansarr = JRequest::getVar('q'.$ques['q_id']); 
				$ans = implode(' ',$ansarr);
				$otherans=$db->getEscaped(JRequest::getVar('q'.$ques['q_id'].'o'));
				if ($pollinfo['poll_emailto']) {
					$qo = 'SELECT opt_txt FROM #__mpoll_questions_opts WHERE published > 0 && opt_id IN ('.implode(',',$ansarr).')';
					$db->setQuery($qo); $opts = $db->loadResultArray();
					foreach ($opt as $o) {
						if ($o->opt_other) $result = $otherans;
						else $result = $o->opt_text;
						$email .= '<br />'.$result;
					}
					$email .= '<br /><br />';
				}
				$q = 'INSERT INTO #__mpoll_results	(res_user,res_poll,res_qid,res_ans,res_ans_other,res_cm) VALUES ("'.$userid.'","'.$pollid.'","'.$ques['q_id'].'","'.$ans.'","'.$otherans.'","'.$lastid.'")';
				$db->setQuery( $q );
				$db->query();
			}
			
		}
		
		if ($pollinfo['poll_emailto']) {
			$mail = &JFactory::getMailer();
			$mail->IsHTML(true);
			$emllist = Array();
			$emllist = explode(",",$pollinfo['poll_emailto']);
			foreach ($emllist as $e) {
				$mail->addRecipient($e,$e);
			}
			$mail->setSender($emllist[0],$emllist[0]);
			$mail->setSubject($pollinfo['poll_emailsubject']);
			$mail->setBody( $email );
			$sent = $mail->Send();
		}
		return 0;
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
