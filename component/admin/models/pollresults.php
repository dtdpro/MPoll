<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelPollResults extends JModelList
{

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'published','q.published','featured'
            );
        }
        parent::__construct($config);
    }
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$pollId = $this->getUserStateFromRequest('com_mpoll.questions.poll', 'poll','');
		$this->setState('poll', $pollId);

        $published = $this->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
        $this->setState('filter.published', $published);

        $featured = $this->getUserStateFromRequest($this->context.'.filter.featured', 'filter_featured', '', 'string');
        $this->setState('filter.featured', $featured);

		// List state information.
		parent::populateState();
	}



    public function getFilterForm($data = [], $loadData = true)
    {
        $form = parent::getFilterForm($data, $loadData);
        $filterQuestions = $this->getQuestions(true);

        foreach ($filterQuestions as $qu) {
            $ans = $this->getUserStateFromRequest($this->context.'.filter.qf_'.$qu->q_id, 'filter_qf_'.$qu->q_id, '', 'string');
            $this->setState('filter.qf_'.$qu->q_id, $ans);
            if ($qu->q_type == 'textbox' || $qu->q_type == 'mailchimp' || $qu->q_type == 'email' || $qu->q_type == 'datedropdown' || $qu->q_type == 'gmap') {
                $formxml = '<field name="qf_'.$qu->q_id.'" type="text" default="'.$ans.'" label="'.$qu->q_name.'" hint="Search '.$qu->q_name.'" description="" />';
                $newField = new \SimpleXMLElement($formxml);
                $form->setField($newField,'filter');
                $this->filter_fields[] = 'qf_'.$qu->q_id.'';
            }
            if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown' || $qu->q_type == 'mcbox' || $qu->q_type=="mlist") {
                $formxml  = '<field name="qf_'.$qu->q_id.'" type="list" default="'.$ans.'" label="'.$qu->q_name.'" description="" onchange="this.form.submit();">';
                $formxml .= '<option value=""><![CDATA[- Select '.$qu->q_name.'-]]></option>';
                foreach ($qu->options as $quo) {
                    $formxml .= '<option value="'.$quo->opt_id.'"><![CDATA['.$quo->opt_text.']]></option>';
                }
                $formxml .= '</field>';
                $newField = new \SimpleXMLElement($formxml);
                $form->setField($newField,'filter');
                $this->filter_fields[] = 'qf_'.$qu->q_id.'';
            }
            if ($qu->q_type == 'cbox') {
                $formxml  = '<field name="qf_'.$qu->q_id.'" type="list" default="'.$ans.'"  description="" onchange="this.form.submit();">';
                $formxml .= '<option value=""><![CDATA[- Select '.$qu->q_name.' -]]></option>';
                $formxml .= '<option value="1">Yes</option>';
                $formxml .= '<option value="0">No</option>';
                $formxml .= '</field>';
                $newField = new \SimpleXMLElement($formxml);
                $form->setField($newField,'filter');
                $this->filter_fields[] = 'qf_'.$qu->q_id.'';
            }
        }

        return $form;
    }
	
	function getResponses($questions)
	{
		$pollid = $this->getState('poll');
        $published = $this->getState('filter.published');
        $featured = $this->getState('filter.featured');
		$db = JFactory::getDBO();

        $filterQuestions = $this->getQuestions(true, false);
        $filterResults = [];
        $filterNada = false;

        foreach ($filterQuestions as $qu) {
            $ans = $this->getState('filter.qf_'.$qu->q_id, '');
            if ($ans != '' && !$filterNada) {
                if ($qu->q_type == 'textbox' || $qu->q_type == 'mailchimp' || $qu->q_type == 'email' || $qu->q_type == 'datedropdown' || $qu->q_type == 'gmap') {
                    $qo=$db->getQuery(true);
                    $qo->select('res_cm');
                    $qo->from('#__mpoll_results');
                    $qo->where('res_ans LIKE "%'.$ans.'%"');
                    $qo->where('res_qid = "'.$qu->q_id.'"');
                    $db->setQuery($qo);
                    $results = $db->loadColumn();
                    $filterResults[] = $results;
                    if (count($results) == 0) $filterNada = true;
                }
                if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown' || $qu->q_type == 'cbox') {
                    $qo=$db->getQuery(true);
                    $qo->select('res_cm');
                    $qo->from('#__mpoll_results');
                    $qo->where('res_ans = "'.$ans.'"');
                    $qo->where('res_qid = "'.$qu->q_id.'"');
                    $db->setQuery($qo);
                    $results = $db->loadColumn();
                    $filterResults[] = $results;
                    if (count($results) == 0) $filterNada = true;
                }
                if ($qu->q_type == 'mcbox' || $qu->q_type=="mlist") {
                    $qo=$db->getQuery(true);
                    $qo->select('res_cm,res_ans');
                    $qo->from('#__mpoll_results');
                    $qo->where('res_qid = "'.$qu->q_id.'"');
                    $db->setQuery($qo);
                    $multiResults = $db->loadObjectList();
                    $filterResult = [];
                    foreach ($multiResults as $multiResult) {
                        $resArray = explode(' ', $multiResult->res_ans);
                        if (in_array($ans, $resArray)) {
                            $filterResult[] = $multiResult->res_cm;
                        }
                    }
                    $filterResults[] = $filterResult;
                    if (count($filterResult) == 0) $filterNada = true;
                }
            }
        }

        $filteredIds = [];
        if (count($filterResults)) $filteredIds = call_user_func_array('array_intersect',$filterResults);

        $data = [];
        if (!$filterNada) {
            //Get Completions
            $q = 'SELECT *';
            $q .= ' FROM #__mpoll_completed as r';
            $q .= ' WHERE r.cm_poll = ' . $pollid;
            if ($published != '') $q .= ' AND r.published = ' . $published;
            if ($featured != '') $q .= ' AND r.featured = ' . $featured;
            if (count($filteredIds)) $q .= ' AND r.cm_id IN (' . implode(',', $filteredIds) . ')';
            $q .= ' ORDER BY r.cm_time DESC';
            $db->setQuery($q);
            $data = $db->loadObjectList();

            //Get Results
            foreach ($data as &$d) {
                $qd = "SELECT * FROM #__mpoll_results WHERE res_cm = " . $d->cm_id;
                $db->setQuery($qd);
                $cmd = $db->loadObjectList();
                foreach ($cmd as $c) {
                    $fn = 'q_' . $c->res_qid;
                    $fno = 'q_' . $c->res_qid . '_other';
                    $d->$fn = $c->res_ans;
                    $d->$fno = $c->res_ans_other;
                }
            }
        }
		
		return $data;
	}	
	
	function getOptions($questions) {
		$db = JFactory::getDBO();
		
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
	
	function getQuestions($filterQuestions = false, $getOptions = true)
	{
		$pollid = $this->getState('poll');
		$query  = ' SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE q_type NOT IN ("captcha","message","header") && q_poll ='.$pollid.' ';
        $query .= ' AND published >= 0 ';
		$query .= 'ORDER BY ordering ASC';
		$db = JFactory::getDBO();
		$db->setQuery($query);
        $items = $db->loadObjectList();
        if ($filterQuestions && $getOptions) {
            foreach ($items as &$q) {
                //Load options
                if ($q->q_type == "multi" || $q->q_type == "mcbox" || $q->q_type == "dropdown" || $q->q_type == "mlist") {
                    $qo=$db->getQuery(true);
                    $qo->select('opt_txt, opt_id, opt_disabled, opt_correct, opt_color, opt_other, opt_selectable, opt_blank');
                    $qo->from('#__mpoll_questions_opts');
                    $qo->where('opt_qid = '.$q->q_id);
                    $qo->where('published > 0');
                    $qo->order('ordering ASC');
                    $db->setQuery($qo);
                    $queriedOptions = $db->loadObjectList();
                    $q->options = $queriedOptions;
                }
            }
        }
		return $items;
	}	
	
	function getUsers() {
		$db = JFactory::getDBO();
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

	public function getPoll() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$pollId = $this->getState('poll');

		if (is_numeric($pollId)) {
			$query->select('*');
			$query->from('#__mpoll_polls');
			$query->where('poll_id = '.(int) $pollId);
			$db->setQuery($query);
			return $db->loadObject();
		}
	}
	
	public function delete(&$pks)
	{
		$pks = (array) $pks;
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		
		//Check permission
		if (!$user->authorise('core.deleterecords', $this->option)) {
			JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING, 'jerror');
			return false;
		}
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			$query = $db->getQuery(true);
			$query->delete();
			$query->from("#__mpoll_completed");
			$query->where('cm_id='.(int)$pk);
			$db->setQuery($query);
			if (!$db->execute()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
			
			$query2 = $db->getQuery(true);
			$query2->delete();
			$query2->from("#__mpoll_results");
			$query2->where('res_cm='.(int)$pk);
			$db->setQuery($query2);
			if (!$db->execute()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
		}
	
		// Clear the component's cache
		$this->cleanCache();
	
		return true;
	}

    public function publish(&$pks, $newState=0)
    {
        $pks = (array) $pks;
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {
            $query = $db->getQuery(true);
            $query->update("#__mpoll_completed");
            $query->set("published = ".$db->escape((int)$newState));
            $query->where('cm_id='.(int)$pk);
            $db->setQuery($query);
            if (!$db->execute()) {
                $this->setError($db->getErrorMsg());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    public function feature(&$pks, $newState=0)
    {
        $pks = (array) $pks;
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {
            $query = $db->getQuery(true);
            $query->update("#__mpoll_completed");
            $query->set("featured = ".$db->escape((int)$newState));
            $query->where('cm_id='.(int)$pk);
            $db->setQuery($query);
            if (!$db->execute()) {
                $this->setError($db->getErrorMsg());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }
}
