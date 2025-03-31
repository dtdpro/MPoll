<?php


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');
use Joomla\Registry\Registry;

class MPollTableEmailtemplate extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(&$db) 
	{
		parent::__construct('#__mpoll_email_templates', 'tmpl_id', $db);
	}


	public function bind($src, $ignore = '')
	{
		return parent::bind($src, $ignore);
	}

	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		$this->modified = $date->toSql();
		$this->modified_by	= $user->get('id');

		if (!$this->poll_id) {
			if (!intval($this->created)) {
				$this->created = $date->toSql();
			}
			if (empty($this->created_by)) {
				$this->created_by = $user->get('id');
			}
		}

		// Attempt to store the user data.
		return parent::store($updateNulls);
	}
	
	public function check()
	{
		// check for valid name
		if (trim($this->tmpl_name) == '') {
			$this->setError(JText::_('COM_MPOLL_ERR_TABLES_TITLE'));
			return false;
		}

		return true;
	}

}