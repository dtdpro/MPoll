<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MPollModelMPolls extends JModelList
{
	
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'p.poll_id', 
				'p.poll_name', 
				'category_title', 
				'published', 
				'access_level',
				'p.poll_created',
				'p.poll_modified',
				'p.poll_type',
				'access',
				'category_id',
			);
		}

		parent::__construct($config);
	}
	
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$accessId = $this->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);

		$published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);

		$categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_mpoll');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('p.poll_name', 'asc');
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
			//Questions Count
			$query = $db->getQuery(true);
			$query->select('count(*)');
			$query->from('#__mpoll_questions');
			$query->where('q_poll='.$i->poll_id);
			$db->setQuery( $query );
			$i->questions = $this->_db->loadResult();
			
			//Results count
			$query = $db->getQuery(true);
			$query->select('count(*)');
			$query->from('#__mpoll_completed');
			$query->where('cm_poll='.$i->poll_id);
			$db->setQuery( $query );
			$i->results = $this->_db->loadResult();
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
		$query->select('p.*');

		// From the hello table
		$query->from('#__mpoll_polls as p');
		
		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = p.access');
		
		// Join over the categories.
		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__categories AS c ON c.id = p.poll_cat');
		
		// Join over the users for the author who added.
		$query->select('ua.name AS adder')
		->join('LEFT', '#__users AS ua ON ua.id = p.poll_created_by');
		
		// Join over the users for the author who modified.
		$query->select('um.name AS modifier')
		->join('LEFT', '#__users AS um ON um.id = p.poll_modified_by');
		
		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('p.access = '.(int) $access);
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('p.published = '.(int) $published);
		} else if ($published === '') {
			$query->where('(p.published IN (0, 1))');
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$query->where('p.poll_cat = '.(int) $categoryId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('p.poll_id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(p.poll_name LIKE '.$search.' )');
			}
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol == 'category_title') {
			$orderCol = 'category_title '.$orderDirn;
		}
		$query->order($db->escape($orderCol.' '.$orderDirn));
				
		return $query;
	}
}
