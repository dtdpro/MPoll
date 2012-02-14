<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MPollModelOptions extends JModelList
{
	
	public function __construct($config = array())
	{
		
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$qId = $this->getUserStateFromRequest($this->context.'.filter.question', 'filter_question'.'');
		$this->setState('filter.question', $qId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_mpoll');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('o.ordering', 'asc');
	}
	
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('o.*');

		// From the hello table
		$query->from('#__mpoll_questions_opts as o');
		
		// Filter by poll.
		$qId = $this->getState('filter.question');
		if (is_numeric($qId)) {
			$query->where('o.opt_qid = '.(int) $qId);
		}
				
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		
		$orderCol = ' o.ordering';
		
		$query->order($db->getEscaped($orderCol.' '.$orderDirn));
				
		return $query;
	}
	
	public function getQuestions() {
		$app = JFactory::getApplication('administrator');
		$pollId = $app->getUserState('com_mpoll.questions.filter.poll');
		$query = 'SELECT q_id AS value, q_text AS text' .
				' FROM #__mpoll_questions' .
				' WHERE q_type IN ("mcbox","multi") && q_poll = '.$pollId .
				' ORDER BY ordering';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}
