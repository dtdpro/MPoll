<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

class MPollModelQuestion extends JModelAdmin
{

	protected function allowEdit($data = array(), $key = 'q_id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_mpoll.question.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}

	public function getTable($type = 'Question', $prefix = 'MPollTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_mpoll.question', 'question', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
	
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_mpoll.edit.question.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
			if ($this->getState('question.q_id') == 0) {
				$app = JFactory::getApplication();
				$data->set('q_poll', JRequest::getInt('q_poll', $app->getUserState('com_mpoll.questions.filter.poll')));
			}
		}
		return $data;
	}
	
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if (empty($table->q_id)) {
			// Set the values

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__mpoll_questions WHERE q_poll = '.$table->q_poll);
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
		$condition[] = 'q_poll = '.(int) $table->q_poll;
		return $condition;
	}
	
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
		$table = $this->getTable();
	
		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);
	
			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
		}
	
		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = JArrayHelper::toObject($properties, 'JObject');
	
		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}
		
		if ($item->q_type == "mailchimp" && $item->q_default) {
			require_once(JPATH_ROOT.'/components/com_mpoll/lib/mailchimp.php');
			$cfg=MPollHelper::getConfig();
			if (!$cfg->mckey) return false;
			if (strstr($item->q_default,"_")){ list($mc_key, $mc_list) = explode("_",$item->q_default,2);	}
			else { $mc_key = $cfg->mckey; $mc_list = $item->q_defaultt; }
			$mc = new MailChimpHelper($mc_key);
			$mclist=$mc->getLists($mc_list);
			if ($mclist[0]) {
				$item->list_mvars = $mc->getListMergeVars($mc_list);
				$item->questions = $this->getQuestions($item->q_poll);
			}
		}
	
		return $item;
	}
	
	
	
	public function copy(&$pks)
	{
		// Initialise variables.
		$user = JFactory::getUser();
		$pks = (array) $pks;
		$table = $this->getTable();
		$otable=$this->getTable("Option","MPollTable");
	
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
	
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
	
			if ($table->load($pk))
			{
				$table->q_id=0;
				
				$this->_db->setQuery('SELECT MAX(ordering) FROM #__mpoll_questions WHERE q_poll = '.$table->q_poll);
				$max = $this->_db->loadResult();
				$table->ordering = $max+1;
				
				if (!$table->check()) {
					$this->setError($table->getError());
					return false;
				}
				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				} else {
	
					$newquestion=$table->q_id;
					$oldquestion=$pk;

					if ($table->q_type == 'mlist' || $table->q_type == 'multi' || $table->q_type == 'mcbox' || $table->q_type == 'dropdown') {
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
	
	protected function getQuestions($pollId) {
		$db=$this->_db;
		$query=$db->getQuery(true);
		$query->select('q_id AS value');
		$query->select('q_name AS text');
		$query->from('#__mpoll_questions');
		$query->where('q_poll = ' . (int) $pollId);
		$query->order('ordering');
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
