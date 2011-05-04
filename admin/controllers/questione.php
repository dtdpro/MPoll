<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class MPollControllerQuestionE extends MPollController
{
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' );
	}

	function edit()
	{
		JRequest::setVar( 'view', 'questione' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	function copy()
	{
		$model = $this->getModel('questione');
		if ($model->copyQ()) {
			$msg = JText::_( 'Question(s) copied!' );
		} else {
			$msg = JText::_( "Don't Copy that Floppy" );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);
	}
	/**
	* Form for moving item(s) to a specific menu
	*/
	function move()
	{
		//$model	=& $this->getModel( 'questione');
		//$view =& $this->getView( 'questione' );
		//$view->setModel( $model, true );
		//$view->moveForm();
		JRequest::setVar( 'view', 'questione' );
		JRequest::setVar( 'layout', 'move'  );
		JRequest::setVar('hidemainmenu', 1);
		parent::display();
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function doMove()
	{
		global $mainframe;

		$newpoll	= JRequest::getVar( 'newpoll' );
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (empty($newpoll))
		{
			$msg = JText::_('Please select a poll from the list');
			$mainframe->enqueueMessage($msg, 'message');
			return $this->execute('move');
		}

		$model	=& $this->getModel( 'questione' );

		if ($model->move($cid, $newpoll)) {
			$msg = JText::sprintf( 'Questions Moved');
		} else {
			$msg = $model->getError();
		}
		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);
	}
	function save()
	{
		$model = $this->getModel('questione');

		if ($model->store($post)) {
			$msg = JText::_( 'Question Saved!' );
		} else {
			$msg = JText::_( 'Error Saving Question' );
		}

		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);
	}
	function remove()
	{
		$model = $this->getModel('questione');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Questions Could not be Deleted' );
		} else {
			$msg = JText::_( 'Question(s) Deleted' );
		}

		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);
	}
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);
	}
	function orderup()
	{
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$pollid = JRequest::getVar('q_poll');
		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
			$msg = 'No Items Selected';
			$this->setRedirect($link, $msg);
			return false;
		}

		$model =& $this->getModel( 'questione' );
		if ($model->orderItem($id, -1,$pollid)) {
			$msg = JText::_( 'Question Moved Up' );
		} else {
			$msg = $model->getError();
		}
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);
	}

	function orderdown()
	{
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$pollid = JRequest::getVar('q_poll');
		if (isset($cid[0]) && $cid[0]) {
			$id = $cid[0];
		} else {
			$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
			$msg = 'No Items Selected';
			$this->setRedirect($link, $msg);
			return false;
		}

		$model =& $this->getModel( 'questione' );
		if ($model->orderItem($id, 1, $pollid)) {
			$msg = JText::_( 'Question Moved Down' );
		} else {
			$msg = $model->getError();
		}
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);

	}
	function saveorder()
	{
		$pollid = JRequest::getVar('q_poll');
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model =& $this->getModel( 'questione' );
		if ($model->setOrder($cid, $pollid)) {
			$msg = JText::_( 'New ordering saved' );
		} else {
			$msg = $model->getError();
		}
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);
	}
	function reqpublish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('questione');
		if(!$model->required($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);
	}


	function requnpublish()
	{
		global $mainframe;

		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('questione');
		if(!$model->required($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$pollid = JRequest::getVar('q_poll');
		$link = 'index.php?option=com_mpoll&view=question&q_poll='.$pollid;
		$this->setRedirect($link, $msg);
	}

}
?>
