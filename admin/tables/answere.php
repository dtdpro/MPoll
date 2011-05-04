<?php

// no direct access
defined('_JEXEC') or die('Restricted access');


class TableAnswerE extends JTable
{
	var $opt_id = null;

	var $opt_qid = null;
	var $opt_txt = null;
	var $ordering = null;
	var $opt_other = null;
	var $opt_correct = null;
	
	function TableAnswerE(& $db) {
		parent::__construct('#__mpoll_questions_opts', 'opt_id', $db);
	}
}
?>
