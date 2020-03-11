<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
		

class MPollViewPollResults extends JViewLegacy
{
	function display($tpl = null)
	{
		$this->questions = $this->get('Questions');
		$this->polltitle = $this->get('PollTitle');
		
		$model = $this->getModel('pollresults');
		$this->items = $model->getResponses($this->questions);
		$this->options = $model->getOptions($this->questions);
		$this->poll = $model->getPoll();
		$this->users = $model->getUsers();
	
		MPOLLHelper::addPollSubmenu(JRequest::getVar('view'),$this->polltitle);
	
		// Set the toolbar
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();
	
		parent::display($tpl);
	}
	
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_MPOLL_MANAGER_POLLRESULTS'), 'MPoll');
		$tbar = JToolBar::getInstance('toolbar');
		$tbar->appendButton('Link','archive','Export CSV','index.php?option=com_mpoll&view=pollresults&format=csv');
		$canDo = MPollHelper::getActions();
		if ($canDo->get('core.deleterecords')) {
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'pollresults.delete', 'COM_MPOLL_MANAGER_POLLRESULTS_DELETERECORDS');
		}
	}
}
