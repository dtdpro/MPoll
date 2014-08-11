<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

class MPollModelMPoll extends JModelAdmin
{
	protected function allowEdit($data = array(), $key = 'poll_id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mpoll.poll.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
	
	public function getTable($type = 'MPoll', $prefix = 'MPollTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_mpoll.mpoll', 'mpoll', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mpoll.edit.mpoll.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
			// Prime some default values.
			if ($this->getState('mpoll.id') == 0) {
				$app = JFactory::getApplication();
				$data->set('poll_cat', JRequest::getInt('poll_cat', $app->getUserState('com_mpoll.mpolls.filter.category_id')));
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
		$qtable=$this->getTable("Question","MPollTable");
		$otable=$this->getTable("Option","MPollTable");
	
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
	
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
	
			if ($table->load($pk))
			{
				$table->poll_id=0;
				$table->published=0;
				$table->poll_name=$table->poll_name.' (Copy)';
				$table->poll_alias="";
				if (!$table->check()) {
					$this->setError($table->getError());
					return false;
				}
				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				} else {
						
					$newpoll = $table->poll_id;
					$oldpoll = $pk;
					
					//Questions and Answers
					$q='SELECT * FROM #__mpoll_questions WHERE q_poll = '.$oldpoll;
					$this->_db->setQuery($q);
					$qus = $this->_db->loadObjectList();
					
					foreach($qus as $qu) {
						if ($qtable->load($qu->q_id)) {
							$qtable->q_id=0;
							$qtable->q_poll=$newpoll;
							if (!$qtable->store()) {
								$this->setError($qtable->getError());
								return false;
							} else {
								$newquestion=$qtable->q_id;
								$oldquestion=$qu->q_id;
								
								if ($qu->q_type == 'mlist' || $qu->q_type == 'multi' || $qu->q_type == 'mcbox' || $qu->q_type == 'dropdown') {
									$qoq='SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$oldquestion;
									$this->_db->setQuery($qoq);
									$qos = $this->_db->loadObjectList();
									foreach($qos as $qo) {
										if ($otable->load($qo->opt_id)) {
											$otable->opt_id=0;
											$otable->opt_qid=$newquestion;
											if (!$otable->store()) {
												$this->setError($otable->getError());
												return false;
											}
										}
										
									}
								}
								
							}
							
						}
					}

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
	
	
	public function delete(&$pks)
	{
		// Initialise variables.
		$dispatcher = JDispatcher::getInstance();
		$pks = (array) $pks;
		$table = $this->getTable();
		$qtable=$this->getTable("Question","MPollTable");
		$otable=$this->getTable("Option","MPollTable");
	
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
	
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
	
			if ($table->load($pk))
			{
	
				if ($this->canDelete($table))
				{
	
					$context = $this->option . '.' . $this->name;
	
					//Questions and Answers
					$q='SELECT * FROM #__mpoll_questions WHERE q_poll = '.$table->poll_id;
					$this->_db->setQuery($q);
					$qus = $this->_db->loadObjectList();
						
					foreach($qus as $qu) {
						if ($qtable->load($qu->q_id)) {
							
							if ($qu->q_type == 'mlist' || $qu->q_type == 'multi' || $qu->q_type == 'mcbox' || $qu->q_type == 'dropdown') {
								$qoq='SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qu->q_id;
								$this->_db->setQuery($qoq);
								$qos = $this->_db->loadObjectList();
								foreach($qos as $qo) {
									if ($otable->load($qo->opt_id)) {
										if (!$otable->delete()) {
											$this->setError($otable->getError());
											return false;
										}
									}
				
								}
							}
							
							if (!$qtable->delete())
							{
								$this->setError($qtable->getError());
								return false;
							}
							
								
						}
					}
					
					//Completions
					$q='DELETE FROM #__mpoll_completed WHERE cm_poll = '.$table->poll_id;
					$this->_db->setQuery($q);
					if (!$this->_db->query()) {
						$this->setError($this->_db->getError());
						return false;
					}

					//Answers
					$q='DELETE FROM #__mpoll_results WHERE res_poll = '.$table->poll_id;
					$this->_db->setQuery($q);
					if (!$this->_db->query()) {
						$this->setError($this->_db->getError());
						return false;
					}
	
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}
	
				}
				else
				{
	
					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();
					if ($error)
					{
						JError::raiseWarning(500, $error);
						return false;
					}
					else
					{
						JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
						return false;
					}
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
