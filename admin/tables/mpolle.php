<?php

// no direct access
defined('_JEXEC') or die('Restricted access');


class TableMPolle extends JTable
{
	var $poll_id = null;

	var $poll_name = null;
	var $poll_desc = null;
	var $poll_start = null;
	var $poll_end = null;
	var $published = null;
	var $poll_only = null;
	var $poll_regonly = null;
	var $poll_rmsg = null;
	var $poll_showresults = null;
	
	function TableMPolle(& $db) {
		parent::__construct('#__mpoll_polls', 'poll_id', $db);
	}
}
?>
