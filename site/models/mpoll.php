<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelMPoll extends JModel
{

	function getPoll($pollid)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid.' && published=1';
		$db->setQuery( $query );
		$pdata = $db->loadAssoc();
		return $pdata;
	}
	function getQuestions($courseid)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE q_poll = '.$courseid.' ORDER BY ordering ASC';
		$db->setQuery( $query );
		$qdata = $db->loadAssocList();
		return $qdata;
	}
	function saveBallot($pollid) {
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$userid = $user->id;
		//save completed
		$qc = 'INSERT INTO #__mpoll_completed (cm_user,cm_poll) VALUES ('.$userid.','.$pollid.')';
		$db->setQuery( $qc );
		$db->query();
		$lastid = $db->insertid();
		//saev answers
		$query = 'SELECT * FROM #__mpoll_questions WHERE q_poll = '.$pollid;
		$db->setQuery( $query );
		$qdata = $db->loadAssocList(); 
		foreach ($qdata as $ques) {
			$otherans=$db->getEscaped(JRequest::getVar('q'.$ques['q_id'].'o'));
			if ($ques['q_type'] != 'mcbox') {
				$ans = $db->getEscaped(JRequest::getVar('q'.$ques['q_id']));
				$q = 'INSERT INTO #__mpoll_results	(res_user,res_poll,res_qid,res_ans,res_ans_other,res_cm) VALUES ("'.$userid.'","'.$pollid.'","'.$ques['q_id'].'","'.$ans.'","'.$otherans.'","'.$lastid.'")';
				$db->setQuery( $q );
				$db->query();
			} else {
				$ansarr = $db->getEscaped(JRequest::getVar('q'.$ques['q_id'])); 
				$ans = implode(' ',$ansarr);
				$q = 'INSERT INTO #__mpoll_results	(res_user,res_poll,res_qid,res_ans,res_ans_other,res_cm) VALUES ("'.$userid.'","'.$pollid.'","'.$ques['q_id'].'","'.$ans.'","'.$otherans.'","'.$lastid.'")';
				$db->setQuery( $q );
				$db->query();
			}
			
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
	
	function getPolls() {
		$query  = ' SELECT * ';
		$query .= ' FROM #__mpoll_polls';
		$query .= ' WHERE published = 1';
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
	

}
