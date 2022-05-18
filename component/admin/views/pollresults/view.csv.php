<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
		

class MPollViewPollResults extends JViewLegacy
{
	function display($tpl = 'csv')
	{
		// Get data from the model
		$model = $this->getModel('pollresults');
		$questions = $model->getQuestions();
		$items = $model->getResponses($questions);
		$opts = $model->getOptions($questions);
		$users = $model->getUsers();
		
		$this->questions = $questions;
		$this->items = $items;
		$this->options = $opts;
		$this->users = $users;
		parent::display($tpl);
	}
}
