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
		$pollId = $this->getUserStateFromRequest($this->context.'.filter.poll', 'filter_poll','');
		$this->setState('filter.poll', $pollId);
		
		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);
		
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_mpoll');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('q.ordering', 'asc');
	}
	
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();
		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}
		// Load the list items.
		$query = $this->_getListQuery();
		try
		{
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
	
		$db = JFactory::getDBO();
		foreach ($items as &$i) {
			//Options Count
			if ($i->q_type=='mlist' ||$i->q_type=='multi' || $i->q_type=='mcbox' || $i->q_type=='dropdown') {
				$query = $db->getQuery(true);
				$query->select('count(*)');
				$query->from('#__mpoll_questions_opts');
				$query->where('opt_qid='.$i->q_id);
				$db->setQuery( $query );
				$i->options = $this->_db->loadResult();
			}
		}
	
		// Add the items to the internal cache.
		$this->cache[$store] = $items;
		return $this->cache[$store];
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
		$pollId = $this->getState('filter.poll');
		if (is_numeric($pollId)) {
			$query->where('q.q_poll = '.(int) $pollId);
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
		$pollId = $this->getState('filter.poll');
		
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
