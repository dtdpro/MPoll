<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelMPoll extends JModel
{
	var $_data;


	function getPolls()
	{
		$query  = ' SELECT * ';
		$query .= ' FROM #__mpoll_polls';
		$query .= ' ORDER BY poll_name ASC';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$data = $db->loadObjectList();
		return $data;
	}

}
