<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelAnsQuest extends JModel
{
	function getResponses($qid,$qtype)
	{
		$db =& JFactory::getDBO();
		if ($qtype == 'multi') {
			$q2  = 'SELECT *,COUNT(r.res_ans) as tally FROM #__mpoll_questions_opts as o ';
			$q2 .= 'LEFT JOIN #__mpoll_results as r ON o.opt_id = r.res_ans ';
			$q2 .= 'WHERE opt_qid = '.$qid.' GROUP BY o.opt_id ORDER BY o.ordering ASC';
		} else {
			$q2  = 'SELECT * FROM #__mpoll_results as a ';
			$q2 .= 'WHERE a.res_qid = "'.$qid.'"';
		}
		$db->setQuery($q2); echo $db->getQuery();
		$data = $db->loadObjectList();
		return $data;
	}	
	function getqInfo($qid)
	{
		$query  = ' SELECT * FROM #__mpoll_questions WHERE q_id ='.$qid;
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$data = $db->loadObject();
		return $data;
	}	
}
