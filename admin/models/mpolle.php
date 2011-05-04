<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class MPollModelMPolle extends JModel
{
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}


	function &getData()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = ' SELECT * FROM #__mpoll_polls '.
					'  WHERE poll_id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->poll_id = 0;
		}
		return $this->_data;
	}
		
	function store()
	{
		$row =& $this->getTable();

		//$data = JRequest::get( 'post' );
		$data->poll_id = JRequest::getVar('poll_id');
		$data->poll_name = JRequest::getVar('poll_name', null, 'default', 'none', 2 );
		$data->poll_desc = JRequest::getVar( 'poll_desc', null, 'default', 'none', 2 );
		$data->poll_start = JRequest::getVar('poll_start');
		$data->poll_end = JRequest::getVar('poll_end');
		$data->published = JRequest::getVar('published');
		$data->poll_only = JRequest::getVar('poll_only');
		$data->poll_regonly = JRequest::getVar('poll_regonly');
		$data->poll_rmsg = JRequest::getVar( 'poll_rmsg', null, 'default', 'none', 2 );
		$data->poll_showresults = JRequest::getVar('poll_showresults');
		//i hate that i have so many fields
		
		
		//JRequest::_cleanVar($data->PostBody,4);
		
		
		// Bind the form fields to the hello table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the hello record is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// Store the web link table to the database
		if (!$row->store()) {
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}

		return true;
	}

	function delete()
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$row =& $this->getTable();

		if (count( $cids ))
		{
			foreach($cids as $cid) {
				if (!$row->delete( $cid )) {
					$this->setError( $row->getErrorMsg() );
					return false;
				}
			}						
		}
		return true;
	}

	function publish($cid = array(), $publish = 1)
	{
		
		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__mpoll_polls'
				. ' SET published = ' . intval( $publish )
				. ' WHERE poll_id IN ( '.$cids.' )'
				
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}		
	}
}
?>
