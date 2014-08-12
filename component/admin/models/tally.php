<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


class MPollModelTally extends JModelList
{
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$pollId = $this->getUserStateFromRequest('com_mpoll.questions.filter.poll', 'filter_poll','');
		$this->setState('filter.poll', $pollId);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_mpoll');
		$this->setState('params', $params);

		// List state information.
		parent::populateState();
	}
	
	function getPoll()
	{
		$pollid = $this->getState('filter.poll');
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid.'';
		$db->setQuery( $query ); 
		$pdata = $db->loadObject();
		return $pdata;
	}
	
	function getQuestions()
	{
		$pollid = $this->getState('filter.poll');
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE Q_type IN ("multi","mcbox","dropdown") && q_poll = '.$pollid.' && published = 1 ORDER BY ordering ASC';
		$db->setQuery( $query ); 
		$qdata = $db->loadObjectList();
		return $qdata;
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
