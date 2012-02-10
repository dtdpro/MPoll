<?php


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

class MPollTableMPoll extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(&$db) 
	{
		parent::__construct('#__mpoll_polls', 'poll_id', $db);
	}
	
	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();
		if ($this->poll_id) {
			// Existing item
			$this->poll_modified		= $date->toMySQL();
			$this->poll_modified_by	= $user->get('id');
		} else {
			// New weblink. A weblink created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!intval($this->poll_created)) {
				$this->poll_created = $date->toMySQL();
			}
			if (empty($this->poll_created_by)) {
				$this->poll_created_by = $user->get('id');
			}
		}

		// Verify that the alias is unique
		$table = JTable::getInstance('MPoll', 'MPollTable');
		if ($table->load(array('poll_alias'=>$this->poll_alias,'poll_cat'=>$this->poll_cat)) && ($table->poll_id != $this->poll_id || $this->poll_id==0)) {
			$this->setError(JText::_('COM_MPOLL_ERROR_UNIQUE_ALIAS'));
			return false;
		}
		// Attempt to store the user data.
		return parent::store($updateNulls);
	}
	
	public function check()
	{
		// check for valid name
		if (trim($this->poll_name) == '') {
			$this->setError(JText::_('COM_MPOLL_ERR_TABLES_TITLE'));
			return false;
		}

		// check for existing name
		$query = 'SELECT poll_id FROM #__mpoll_poll WHERE poll_name = '.$this->_db->Quote($this->pol_name).' AND poll_cat = '.(int) $this->poll_cat;
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::_('COM_MPOLL_ERR_TABLES_NAME'));
			return false;
		}

		if (empty($this->poll_alias)) {
			$this->poll_alias = $this->poll_name;
		}
		$this->poll_alias = JApplication::stringURLSafe($this->poll_alias);
		if (trim(str_replace('-','',$this->poll_alias)) == '') {
			$this->poll_alias = JFactory::getDate()->format("Y-m-d-H-i-s");
		}

		// Check the end date is not earlier than start date.
		if (intval($this->poll_end) > 0 && $this->poll_end < $this->poll_start) {
			// Swap the dates.
			$temp = $this->poll_start;
			$this->poll_start = $this->poll_end;
			$this->poll_end = $temp;
		}

		return true;
	}

}