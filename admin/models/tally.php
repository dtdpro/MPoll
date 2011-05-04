<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


class MPollModelTally extends JModel
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

}
