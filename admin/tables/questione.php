<?php
// no direct access
defined('_JEXEC') or die('Restricted access');


class TableQuestionE extends JTable
{
	var $q_id = null;

	var $q_poll = null;
	var $ordering = null;
	var $q_text = null;
	var $q_type = null;
	var $q_req = null;
	var $q_charttype = null;

	function TableQuestionE(& $db) {
		parent::__construct('#__mpoll_questions', 'q_id', $db);
	}
}
?>
