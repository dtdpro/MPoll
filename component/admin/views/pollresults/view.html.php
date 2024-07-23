<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
		

class MPollViewPollResults extends JViewLegacy
{
	function display($tpl = null)
	{
		$jinput = JFactory::getApplication()->input;
		$this->questions = $this->get('Questions');
		$this->polltitle = $this->get('PollTitle');
		
		$model = $this->getModel('pollresults');
		$this->items = $model->getResponses($this->questions);
		$this->options = $model->getOptions($this->questions);
		$this->poll = $model->getPoll();
		$this->users = $model->getUsers();

		if (JVersion::MAJOR_VERSION == 3) MPOLLHelper::addPollSubmenu($jinput->getVar('view'),$this->polltitle);
	
		// Set the toolbar
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();
	
		parent::display($tpl);
	}
	
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_MPOLL_MANAGER_POLLRESULTS'), 'MPoll');
		//$tbar = JToolBar::getInstance('toolbar');
		//$tbar->appendButton('Link','archive','Export CSV','index.php?option=com_mpoll&view=pollresults&format=csv');
		JToolbarHelper::link('index.php?option=com_mpoll&view=pollresults&format=csv','Export CSV','archive');
		$canDo = MPollHelper::getActions();
        if ($canDo->get('core.edit.state')) {
            JToolBarHelper::custom('pollresults.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
            JToolBarHelper::custom('pollresults.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
        }
        if ($canDo->get('core.deleterecords')) {
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'pollresults.delete', 'COM_MPOLL_MANAGER_POLLRESULTS_DELETERECORDS');
		}
		if (JVersion::MAJOR_VERSION >= 4) JToolbarHelper::link('index.php?option=com_mpoll&view=mpolls','Return to Polls','chevron-left');
	}
}
