<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelPollResults extends JModelList
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
	
	function getResponses($questions)
	{
		$pollid = $this->getState('filter.poll');
		$db =& JFactory::getDBO();
		
		//Get Completions
		$q = 'SELECT *';
		$q .= ' FROM #__mpoll_completed as r';
		$q .= ' WHERE r.cm_poll = '.$pollid.' ORDER BY r.cm_time DESC';
		$db->setQuery($q);
		$data = $db->loadObjectList();
		
		//Get Results
		foreach ($data as &$d) {
			$qd = "SELECT * FROM #__mpoll_results WHERE res_cm = ".$d->cm_id;
			$db->setQuery($qd);
			$cmd = $db->loadObjectList();
			foreach ($cmd as $c) {
				$fn='q_'.$c->res_qid;
            	$fno='q_'.$c->res_qid.'_other';
				$d->$fn=$c->res_ans;
				$d->$fno=$c->res_ans_other;
			}
		}
		
		return $data;
	}	
	
	function getOptions($questions) {
		$db =& JFactory::getDBO();
		
		//Get QIDs
		$qids=array();
		foreach ($questions as $q) {
			$qids[]=$q->q_id;
		}
		
		$qo = "SELECT * FROM #__mpoll_questions_opts WHERE opt_qid IN(".implode(",",$qids).")";
		$db->setQuery($qo);
		$ores = $db->loadObjectList();
		
		$odata = array();
		foreach ($ores as $o) {
			$odata[$o->opt_id] = $o->opt_txt;
		}
		
		return $odata;
	}
	
	function getQuestions()
	{
		$pollid = $this->getState('filter.poll');
		$query  = ' SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE q_type NOT IN ("captcha","message","header") && q_poll ='.$pollid.' ';
		$query .= 'ORDER BY ordering ASC';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$data = $db->loadObjectList();
		return $data;
	}	
	
	function getUsers() {
		$db =& JFactory::getDBO();
		$qo = "SELECT * FROM #__users";
		$db->setQuery($qo);
		$ures = $db->loadObjectList();
		
		$udata = array();
		foreach ($ures as $u) {
			$udata[$u->id] = $u;
		}
		
		return $udata;
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
