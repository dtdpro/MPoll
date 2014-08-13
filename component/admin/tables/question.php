<?php


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

class MPollTableQuestion extends JTable
{
	function __construct(&$db) 
	{
		parent::__construct('#__mpoll_questions', 'q_id', $db);
	}
	
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}
	
		return parent::bind($array, $ignore);
	}

}