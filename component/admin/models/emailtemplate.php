<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

use Joomla\Utilities\ArrayHelper;

class MPollModelEmailtemplate extends JModelAdmin
{

	protected function allowEdit($data = array(), $key = 'tmpl_id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mpoll.emailtemplate.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}

	public function getTable($type = 'Emailtemplate', $prefix = 'MPollTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_mpoll.emailtemplate', 'emailtemplate', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_mpoll.edit.emailtemplate.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
			if ($this->getState('emailtemplate.tmpl_id') == 0) {
				$app = JFactory::getApplication();
				$data->set('tmpl_poll', $jinput->getInt('tmpl_poll', $app->getUserState('com_mpoll.emailtemplates.poll')));
			}
		}
		return $data;
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
				$table->tmpl_id=0;

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

    public function getQuestions($pollid) {
        $query = 'SELECT q_id AS value, q_text AS text' .
            ' FROM #__mpoll_questions' .
            ' WHERE q_poll = ' . $pollid .
            ' ORDER BY ordering';
        $this->_db->setQuery($query);
        return $this->_db->loadObjectList();
    }
}
