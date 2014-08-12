<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MPollModelOptions extends JModelList
{
	
	public function __construct($config = array())
	{
		
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'ordering', 'q.ordering',
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$qId = $this->getUserStateFromRequest($this->context.'.filter.question', 'filter_question'.'');
		$this->setState('filter.question', $qId);
		
		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);

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

		// From the options table
		$query->from('#__mpoll_questions_opts as o');
		
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('o.published = '.(int) $published);
		} else if ($published === '') {
			$query->where('(o.published IN (0, 1))');
		}

		// Filter by poll.
		$qId = $this->getState('filter.question');
		if (is_numeric($qId)) {
			$query->where('o.opt_qid = '.(int) $qId);
		}
				
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		
		$orderCol = ' o.ordering';
		
		$query->order($db->escape($orderCol.' '.$orderDirn));
				
		return $query;
	}

	public function getQuestionTitle() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$qId = $this->getState('filter.question');
	
		if (is_numeric($qId)) {
			$query->select('q_name');
			$query->from('#__mpoll_questions');
			$query->where('q_id = '.(int) $qId);
			$db->setQuery($query);
			return $db->loadResult();
		} else {
			return "NO POLL";
		}
	}
}
