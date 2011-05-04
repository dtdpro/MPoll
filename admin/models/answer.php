<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class MPollModelAnswer extends JModel
{
	var $_data;
	var $_total = null;
	var $_pagination = null;

	function __construct()
	{
		parent::__construct();

		global $mainframe, $context;
		$context='com_mpoll.answer.';
		$limit			= $mainframe->getUserStateFromRequest( $context.'limit', 'limit', $mainframe->getCfg('list_limit'), 0);
		$limitstart = $mainframe->getUserStateFromRequest( $context.'limitstart', 'limitstart', 0 );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}
	function _buildQuery()
	{
		$questionid = JRequest::getVar('opt_qid');
		$query = ' SELECT * '
			. ' FROM #__mpoll_questions_opts'
			. ' WHERE opt_qid = '.$questionid.' ORDER BY ordering';

		return $query;
	}

	function getData()
	{
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query );
		}

		return $this->_data;
	}
	function getTotal()
	{
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}
	
	function getPagination()
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}	
	
	function getPollInfo($pollid)
	{
		$q='SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid;
		$db =& $this->getDBO();
		$db->setQuery( $q );
		return $db->loadObject();
	}
	function getQInfo($qid)
	{
		$q='SELECT * FROM #__mpoll_questions WHERE 	q_id = '.$qid;
		$db =& $this->getDBO();
		$db->setQuery( $q );
		return $db->loadObject();
	}
}
