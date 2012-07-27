<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
		

class MPollViewPollResults extends JView
{
	function display($tpl = 'csv')
	{
		$pid = JRequest::getVar('poll');
		
		// Get data from the model
		$model = $this->getModel('pollresults');
		$questions = $model->getQuestions($pid);
		$items = $model->getResponses($pid,$questions);
		
		$this->assignRef('questions',		$questions);
		$this->assignRef('items',		$items);
		parent::display($tpl);
	}
}
