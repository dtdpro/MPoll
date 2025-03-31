<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');


class MPollModelEmailtemplates extends JModelList
{
	
	public function __construct($config = array())
	{		
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'published','q.published','poll','q.poll'
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
		parent::populateState('q.tmpl_name', 'asc');

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
	
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('q.*');

		// From the hello table
		$query->from('#__mpoll_email_templates as q');
		
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
			$query->where('q.tmpl_poll = '.(int) $pollId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('q.tmpl_id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('(q.tmpl_name LIKE '.$search.' )');
			}
		}
				
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		
		$orderCol = ' q.tmpl_name';
		
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
