<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class MPollControllerAnswerE extends MPollController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' );
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'answere' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('answere');

		if ($model->store($post)) {
			$msg = JText::_( 'Answer Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Answer' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$questionid = JRequest::getVar('opt_qid');
		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=answer&q_poll='.$pollid.'&opt_qid='.$questionid;
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('answere');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Answers Could not be Deleted' );
		} else {
			$msg = JText::_( 'Answer(s) Deleted' );
		}

		$questionid = JRequest::getVar('opt_qid');
		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=answer&q_poll='.$pollid.'&opt_qid='.$questionid;
		$this->setRedirect($link, $msg);
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$questionid = JRequest::getVar('opt_qid');
		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=answer&q_poll='.$pollid.'&opt_qid='.$questionid;
		$this->setRedirect($link, $msg);
	}

	function orderup()
	{
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$pollid = JRequest::getVar('q_poll');
		$questionid = JRequest::getVar('opt_qid');
		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$link = 'index.php?option=com_mpoll&view=answer&q_poll='.$pollid.'&opt_qid='.$questionid;
			$msg = 'No Items Selected';
			$this->setRedirect($link, $msg);
			return false;
		}

		$model =& $this->getModel( 'answere' );
		if ($model->orderItem($id, -1,$questionid)) {
			$msg = JText::_( 'Answer Moved Up' );
		} else {
			$msg = $model->getError();
		}
		$link = 'index.php?option=com_mpoll&view=answer&q_poll='.$pollid.'&opt_qid='.$questionid;
		$this->setRedirect($link, $msg);
	}

	function orderdown()
	{
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$pollid = JRequest::getVar('q_poll');
		$questionid = JRequest::getVar('opt_qid');
		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$link = 'index.php?option=com_mpoll&view=answer&q_poll='.$pollid.'&opt_qid='.$questionid;
			$msg = 'No Items Selected';
			$this->setRedirect($link, $msg);
			return false;
		}

		$model =& $this->getModel( 'answere' );
		if ($model->orderItem($id, 1, $questionid)) {
			$msg = JText::_( 'Answer Moved Down' );
		} else {
			$msg = $model->getError();
		}
		$link = 'index.php?option=com_mpoll&view=answer&q_poll='.$pollid.'&opt_qid='.$questionid;
		$this->setRedirect($link, $msg);

	}
	function saveorder()
	{
		$pollid = JRequest::getVar('q_poll');
		$questionid = JRequest::getVar('opt_qid');
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model =& $this->getModel( 'answere' );
		if ($model->setOrder($cid, $questionid)) {
			$msg = JText::_( 'New ordering saved' );
		} else {
			$msg = $model->getError();
		}
		$link = 'index.php?option=com_mpoll&view=answer&q_poll='.$pollid.'&opt_qid='.$questionid;
		$this->setRedirect($link, $msg);
	}
	

}
?>
