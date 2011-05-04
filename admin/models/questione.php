<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class MPollModelQuestionE extends JModel
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
			$query = ' SELECT * FROM #__mpoll_questions '.
					'  WHERE q_id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
		}
		return $this->_data;
	}
	
	function copyQ() {
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' ); 
		JArrayHelper::toInteger($cid);
		$row =& $this->getTable();
		
		foreach($cid as $qu) {
			$row->load($qu);
			$row->q_id=0;
			$row->ordering=$row->getNextOrder('q_poll = '.$row->q_poll);
			if(!$row->store()) return false;
			if ($row->q_type == 'multi' || $row->q_type == 'mcbox') {
				$newid = $row->q_id;
				$qoq='SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qu;
				$this->_db->setQuery($qoq); 
				$qos = $this->_db->loadObjectList();
				foreach($qos as $qo) {
					$q  = 'INSERT INTO #__mpoll_questions_opts (opt_qid,opt_txt,ordering,opt_other,opt_correct) ';
					$q .= 'VALUES ("'.$newid.'","'.$qo->opt_txt.'","'.$qo->ordering.'","'.$qo->opt_other.'","'.$qo->opt_correct.'")';
					$this->_db->setQuery($q);
					if (!$this->_db->query($q)) {
						return false;
					}
				}	
			}
		}						
		return true;
	}
	
	function move($cid, $newpoll)
	{
		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__mpoll_questions SET q_poll = '.$newpoll.' WHERE q_id IN ('.$cids.')';
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;		
	}
	
	function store()
	{
		$row =& $this->getTable();

		$data->q_id = JRequest::getVar('q_id');
		$data->q_poll = JRequest::getVar('q_poll');
		$data->ordering = JRequest::getVar('ordering');
		if (!$data->ordering) $data->ordering = $row->getNextOrder('q_poll = '.$data->q_poll);
		$data->q_text = JRequest::getVar('q_text',null,'default','none',4);
		$data->q_type = JRequest::getVar('q_type');
		$data->q_charttype = JRequest::getVar('q_charttype');
		$data->q_req = JRequest::getVar('q_req');

		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
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

	function setOrder($items,$poll) {
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
		$row->reorder('q_poll = '.$poll);
		return true;
	}
	function orderItem($item, $movement, $poll)
	{
		$row =& $this->getTable();
		$row->load( $item );
		if (!$row->move( $movement, 'q_poll = '.$poll )) {
			$this->setError($row->getError());
			return false;
		}
		return true;
	}

	function required($cid = array(), $publish = 1)
	{
		
		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__mpoll_questions'
				. ' SET q_req = ' . intval( $publish )
				. ' WHERE q_id IN ( '.$cids.' )'
				
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;		
	}
	
	function getPollList() {
		$q='SELECT poll_id,poll_name FROM #__mpoll_polls';
		$this->_db->setQuery($q);
		return $this->_db->loadObjectList();
	
	}
	function getQuestionsFromRequest()
	{
		static $items;

		if (isset($items)) {
			return $items;
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			$this->setError(JText::_( 'Select a question to move'));
			return false;
		}

		// Query to list the selected menu items
		$db =& $this->getDBO();
		$cids = implode( ',', $cid );
		$query = 'SELECT `q_id`, `q_text`' .
				' FROM `#__mpoll_questions`' .
				' WHERE `q_id` IN ( '.$cids.' )';

		$db->setQuery( $query );
		$items = $db->loadObjectList();

		return $items;
	}

}
?>
