<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


class MPollModelTally extends JModelLegacy
{
	function getPoll($pollid)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid.'';
		$db->setQuery( $query ); 
		$pdata = $db->loadObject();
		return $pdata;
	}
	function getQuestions($pollid)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE Q_type IN ("multi","mcbox","dropdown") && q_poll = '.$pollid.' && published = 1 ORDER BY ordering ASC';
		$db->setQuery( $query ); 
		$qdata = $db->loadObjectList();
		return $qdata;
	}

}
