<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

class modMPollHelper
{
	function getPoll($pollid)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid.' && published=1';
		$db->setQuery( $query );
		$pdata = $db->loadObject();
		return $pdata;
	}

	function getQuestions($pollid)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE published = 1 && q_poll = '.$pollid.' && q_type IN ("multi","cbox","textbox","textar") ORDER BY ordering ASC';
		$db->setQuery( $query );
		$qdata = $db->loadObjectList();
		foreach ($qdata as &$q) {
			if ($q->q_type == "multi" || $q->q_type == "mcbox" || $q->q_type == "dropdown") {
				$qo="SELECT opt_txt as text, opt_id as value, opt_disabled, opt_correct, opt_color FROM #__mpoll_questions_opts WHERE opt_qid = ".$q->q_id." && published > 0 ORDER BY ordering ASC";
				$db->setQuery($qo);
				$q->options = $db->loadObjectList();
			}
		}
		return $qdata;
	}
	function getCasted($pollid) {
		$db =& JFactory::getDBO();
		//$sewn = JFactory::getSession();
		//$sessionid = $sewn->getId();
		$user =& JFactory::getUser();
		$userid = $user->id;
		$query = 'SELECT * FROM #__mpoll_results WHERE res_user="'.$userid.'" && res_poll="'.$pollid.'"';
		$db->setQuery($query);
		$data = $db->loadAssoc();
		if (count($data) > 0) return true;
		else return false;
	}
}
?>
