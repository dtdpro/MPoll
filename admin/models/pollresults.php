<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelPollResults extends JModel
{
	function getResponses($cid,$questions)
	{
		$db =& JFactory::getDBO();
		$q = 'SELECT DISTINCT r.*,m.*';
		foreach ($questions as $qu) {
			$q .= ',q'.$qu->q_id;
			if ($qu->q_type=='multi' || $qu->q_type=='dropdown') $q .= 'a.opt_txt';
			else $q .= '.res_ans';
			$q .= ' as q'.$qu->q_id.'ans';
			$q .= ',q'.$qu->q_id.'.res_ans_other as q'.$qu->q_id.'anso';
		}
		$q .= ' FROM #__mpoll_completed as r';
		//$q .= ' LEFT JOIN #__mpoll_courses as c ON r.course = c.id';
		$q .= ' LEFT JOIN #__users as m ON r.cm_user = m.id';
		foreach ($questions as $qu) {
			$q .= ' LEFT JOIN #__mpoll_results as q'.$qu->q_id.' ON q'.$qu->q_id.'.res_qid = '.$qu->q_id.' && r.cm_id = q'.$qu->q_id.'.res_cm';
			if ($qu->q_type=='multi' || $qu->q_type=='dropdown') $q .= ' LEFT JOIN #__mpoll_questions_opts as q'.$qu->q_id.'a ON q'.$qu->q_id.'a.opt_id=q'.$qu->q_id.'.res_ans ';
		}
		$q .= ' WHERE r.cm_poll = '.$cid.' ORDER BY r.cm_time DESC';
		$db->setQuery($q);
		$data = $db->loadAssocList();
		return $data;
	}	
	function getQuestions($cid)
	{
		$query  = ' SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE q_poll ='.$cid.' ';
		$query .= 'ORDER BY ordering ASC';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$data = $db->loadObjectList();
		return $data;
	}	
}
