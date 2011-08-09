<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MPollModelQuestions extends JModelList
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
		$pollId = $this->getUserStateFromRequest($this->context.'.filter.poll', 'filter_poll', JRequest::getInt('q_poll',0));
		$this->setState('filter.poll', $pollId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_mpoll');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('q.ordering', 'asc');
	}
	
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('q.*');

		// From the hello table
		$query->from('#__mpoll_questions as q');
		
		// Filter by poll.
		$pollId = $this->getState('filter.poll');
		if (is_numeric($pollId)) {
			$query->where('q.q_poll = '.(int) $pollId);
		}
				
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		
		$orderCol = ' q.ordering';
		
		$query->order($db->getEscaped($orderCol.' '.$orderDirn));
				
		return $query;
	}
	
	public function getPolls() {
		$query = 'SELECT poll_id AS value, poll_name AS text' .
				' FROM #__mpoll_polls' .
				' ORDER BY poll_name';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
}
