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
		$pollId = $this->getUserStateFromRequest('com_mpoll.questions.poll', 'poll','');
		$this->setState('poll', $pollId);
		
		// Load the parameters.
		$params = JComponentHelper::getParams('com_mpoll');
		$this->setState('params', $params);

		// List state information.
		parent::populateState();
	}
	
	function getPoll()
	{
		$pollid = $this->getState('poll');
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid.'';
		$db->setQuery( $query ); 
		$pdata = $db->loadObject();
		return $pdata;
	}
	
	function getQuestions()
	{
		$pollid = $this->getState('poll');
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE Q_type IN ("multi","mcbox","dropdown") && q_poll = '.$pollid.' && published = 1 ORDER BY ordering ASC';
		$db->setQuery( $query ); 
		$qdata = $db->loadObjectList();
		foreach ($qdata as &$q) {
			//Get options
			if ($q->q_type == "multi" || $q->q_type == "mcbox" || $q->q_type == "dropdown" || $q->q_type == "mlist") {
				$qo="SELECT opt_txt as text, opt_id as value, opt_disabled, opt_correct, opt_color, opt_other, opt_selectable FROM #__mpoll_questions_opts WHERE opt_qid = ".$q->q_id." && published > 0 ORDER BY ordering ASC";
				$db->setQuery($qo);
				$q->options = $db->loadObjectList();
			}
			$registry = new JRegistry();
			$registry->loadString($q->params);
			$q->params = $registry->toObject();
		}
		return $qdata;
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
