<?php


// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');
use Joomla\Registry\Registry;

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


	public function bind($src, $ignore = '')
	{
		if (isset($src['poll_results_emails']) && is_array($src['poll_results_emails']))
		{
			$registry = new Registry;
			$registry->loadArray($src['poll_results_emails']);
			$src['poll_results_emails'] = (string) $registry;
		}
		return parent::bind($src, $ignore);
	}

	public function store($updateNulls = false)
	{
		$date	= JFactory::getDate();
		$user	= JFactory::getUser();

		$this->poll_modified = $date->toSql();
		$this->poll_modified_by	= $user->get('id');

		if (!$this->poll_id) {
			if (!intval($this->poll_created)) {
				$this->poll_created = $date->toSql();
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
		$query = 'SELECT poll_id FROM #__mpoll_polls WHERE poll_name = '.$this->_db->Quote($this->pol_name).' AND poll_cat = '.(int) $this->poll_cat;
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::_('COM_MPOLL_ERR_TABLES_NAME'));
			return false;
		}

		if (empty($this->poll_alias)) {
			$this->poll_alias = $this->poll_name;
		}
		$this->poll_alias = JApplicationHelper::stringURLSafe($this->poll_alias);
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