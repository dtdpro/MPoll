<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

class MPollModelOption extends JModelAdmin
{
	protected function allowEdit($data = array(), $key = 'opt_id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mpoll.option.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
	
	public function getTable($type = 'Option', $prefix = 'MPollTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_mpoll.option', 'option', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	
	protected function loadFormData() 
	{
		$jinput = JFactory::getApplication()->input;
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mpoll.edit.option.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
			if ($this->getState('option.opt_id') == 0) {
				$app = JFactory::getApplication();
				$data->set('opt_qid', $jinput->getInt('opt_qid', $app->getUserState('com_mpoll.options.question')));
			}
		}
		return $data;		
	}
	
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if (empty($table->opt_id)) {
			// Set the values

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__mpoll_questions_opts WHERE opt_qid = '.$table->opt_qid);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		else {
			// Set the values
		}
	}
	
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'opt_qid = '.(int) $table->opt_qid;
		return $condition;
	}
	
	public function copy(&$pks)
	{
		// Initialise variables.
		$user = JFactory::getUser();
		$pks = (array) $pks;
		$table = $this->getTable();
	
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
	
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
	
			if ($table->load($pk))
			{
				$table->opt_id=0;
	
				$this->_db->setQuery('SELECT MAX(ordering) FROM #__mpoll_questions_opts WHERE opt_qid = '.$table->opt_qid);
				$max = $this->_db->loadResult();
				$table->ordering = $max+1;
	
				if (!$table->check()) {
					$this->setError($table->getError());
					return false;
				}
				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				} 
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}
	
		// Clear the component's cache
		$this->cleanCache();
	
		return true;
	}
}
