<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MPollViewTally extends JViewLegacy
{
	function display($tpl = null)
	{
		
		$this->qdata = $this->get('Questions');
		$this->pdata = $this->get('Poll');
		$this->polltitle = $this->get('PollTitle');
		
		MPOLLHelper::addPollSubmenu(JRequest::getVar('view'),$this->polltitle);

		// Set the toolbar
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
	}
	
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_MPOLL_MANAGER_TALLY'), 'MPoll');
	}
}
