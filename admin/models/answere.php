<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class MPollModelAnswerE extends JModel
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
			$query = ' SELECT * FROM #__mpoll_questions_opts '.
					'  WHERE opt_id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->opt_id = 0;
		}
		return $this->_data;
	}
	
	function store()
	{
		$row =& $this->getTable();

		//$data = JRequest::get( 'post' );
		$data->opt_id = JRequest::getVar('opt_id');
		$data->opt_qid = JRequest::getVar('opt_qid');
		$data->opt_txt = JRequest::getVar('opt_txt',null,'default','none',4);
		$data->ordering = JRequest::getVar('ordering');
		if (!$data->ordering) $data->ordering = $row->getNextOrder('opt_qid = '.$data->opt_qid);
		$data->opt_other = JRequest::getVar('opt_other');
		$data->opt_correct = JRequest::getVar('opt_correct');
		
		
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
			$this->setError( $row->getErrorMsg() );
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


	function setOrder($items,$question) {
		$total		= count( $items );
		$row		=& $this->getTable();

		$order		= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($order);

		// update ordering values
		for( $i=0; $i < $total; $i++ ) {
			$row->load( $items[$i] );
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($row->getError());
					return false;
				}
			} // if
		} // for
		$row->reorder('opt_qid = '.$question);
		return true;
	}
	function orderItem($item, $movement, $question)
	{
		$row =& $this->getTable();
		$row->load( $item );
		if (!$row->move( $movement, 'opt_qid = '.$question )) {
			$this->setError($row->getError());
			return false;
		}
		return true;
	}


}
?>
