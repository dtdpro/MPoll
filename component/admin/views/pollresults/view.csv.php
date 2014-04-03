<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
		

class MPollViewPollResults extends JViewLegacy
{
	function display($tpl = 'csv')
	{
		$pid = JRequest::getVar('poll');
		
		// Get data from the model
		$model = $this->getModel('pollresults');
		$questions = $model->getQuestions($pid);
		$items = $model->getResponses($pid,$questions);
		$opts = $model->getOptions($questions);
		$users = $model->getUsers();
		
		$this->assignRef('questions',$questions);
		$this->assignRef('items',$items);
		$this->assignRef('options',$opts);
		$this->assignRef('users',$users);
		parent::display($tpl);
	}
}
