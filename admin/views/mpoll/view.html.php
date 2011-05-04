<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MPollViewMPoll extends JView
{
	function display($tpl = null)
	{
		JToolBarHelper::title(   JText::_( 'MPoll Polls Manager' ), 'generic.png' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		$model = $this->getModel('mpoll');
		// Get data from the model
		$items		= $model->getPolls();

		$this->assignRef('items',		$items);

		parent::display($tpl);
	}
}
