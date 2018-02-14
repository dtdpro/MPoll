<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

class modMPollHelper
{
	function getPoll($pollid)
	{
		$db =& JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_polls');
		$query->where('poll_id = '.$pollid);
		$query->where('published > 0');
		$db->setQuery( $query );
		$pdata = $db->loadObject();
		return $pdata;
	}

	function getQuestions($pollid)
	{
		$db = JFactory::getDBO();
		$app=Jfactory::getApplication();
		$query=$db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_questions');
		$query->where('published > 0');
		$query->where('q_poll = '.$pollid);
		$query->where('q_type IN ("mcbox","mlist","email","dropdown","multi","cbox","textbox","textar","attach")');
		$query->order('ordering ASC');
		$db->setQuery( $query );
		$qdata = $db->loadObjectList();
		foreach ($qdata as &$q) {
			//Load options
			if ($q->q_type == "multi" || $q->q_type == "mcbox" || $q->q_type == "dropdown" || $q->q_type == "mlist") {
				$qo=$db->getQuery(true);
				$qo->select('opt_txt as text, opt_id as value, opt_disabled, opt_correct, opt_color, opt_other, opt_selectable');
				$qo->from('#__mpoll_questions_opts');
				$qo->where('opt_qid = '.$q->q_id);
				$qo->where('published > 0');
				$qo->order('ordering ASC');
				$db->setQuery($qo);
				$q->options = $db->loadObjectList();
			}

			//Load Question Params
			$registry = new JRegistry();
			$registry->loadString($q->params);
			$q->params = $registry->toObject();

			//Set default/saved values
			$fn='q_'.$q->q_id;
			$value = $q->q_default;
			if ($q->q_type == 'mlimit' || $q->q_type == 'multi' || $q->q_type == 'dropdown' || $q->q_type == 'mcbox' || $q->q_type == 'mlist') {
				$q->value=explode(" ",$value);
				$q->other = $other;
			} else if ($q->q_type == 'mailchimp' || $q->q_type == 'cbox' || $q->q_type == 'yesno') {
				$q->value=$value;
			} else if ($q->q_type == 'birthday') {
				$q->value=$value;
			} else if ($q->q_type != 'captcha') {
				$q->value=$value;
			}
		}
		return $qdata;
	}

	function getCasted($pollid) {
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		if (!$user->id) return false;

		$query=$db->getQuery(true);
		$query->select('cm_id');
		$query->from('#__mpoll_completed');
		$query->where('cm_user='.$user->id);
		$query->where('cm_poll='.$pollid);
		$db->setQuery($query);
		$data = $db->loadColumn();

		if (count($data)) return true;
		else return false;
	}
}
?>