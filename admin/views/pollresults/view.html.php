<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
		

class MPollViewPollResults extends JView
{
	function display($tpl = null)
	{
		$pid = JRequest::getVar('poll');
		JToolBarHelper::title(   JText::_( 'By User Poll Results' ), 'continued' );
		$tbar =& JToolBar::getInstance('toolbar');
		$tbar->appendButton('Link','archive','Export CSV','index.php?option=com_mpoll&controller=pollresults&task=csvme&poll='.$pid.'&format=raw');
		JToolBarHelper::back('Polls','index.php?option=com_mpoll');
		// Get data from the model
		$model = $this->getModel('pollresults');
		$questions = $model->getQuestions($pid);
		$items = $model->getResponses($pid,$questions);
		
		$this->assignRef('questions',		$questions);
		$this->assignRef('items',		$items);
		parent::display($tpl);
	}
}
