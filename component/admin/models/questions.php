<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MPollModelQuestions extends JModelList
{
	
	public function __construct($config = array())
	{		
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'ordering', 'q.ordering','published','q.published'
			);
		}
		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');
		
		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_mpoll');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('q.ordering', 'asc');

		// Load the filter state.
		$pollId = $this->getUserStateFromRequest($this->context.'.poll', 'poll','');
		$this->setState('poll', $pollId);
	}
	
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
	
		return parent::getStoreId($id);
	}
	
	public function getItems()
	{
		$items = parent::getItems();
	
		foreach ($items as &$i) {
			//Options Count
			if ($i->q_type=='mlist' ||$i->q_type=='multi' || $i->q_type=='mcbox' || $i->q_type=='dropdown') {
				$query = $this->_db->getQuery(true);
				$query->select('count(*)');
				$query->from('#__mpoll_questions_opts');
				$query->where('opt_qid='.$i->q_id);
				$this->_db->setQuery( $query );
				$i->options = $this->_db->loadResult();
			}
		}

		return $items;
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
		
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('q.published = '.(int) $published);
		} else if ($published === '') {
			$query->where('(q.published IN (0, 1))');
		}

		// Filter by poll.
		$pollId = $this->getState('poll');
		if (is_numeric($pollId)) {
			$query->where('q.q_poll = '.(int) $pollId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('q.q_id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(q.q_name LIKE '.$search.' )');
			}
		}
				
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		
		$orderCol = ' q.ordering';
		
		$query->order($db->escape($orderCol.' '.$orderDirn));
				
		return $query;
	}
	
	public function getPollTitle() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$pollId = $this->getState('poll');
		
		if (is_numeric($pollId)) {
			$query->select('poll_name');
			$query->from('#__mpoll_polls');
			$query->where('poll_id = '.(int) $pollId);
			$db->setQuery($query);
			return $db->loadResult();
		} else {
			return "NO POLL";
		}
	}
}
