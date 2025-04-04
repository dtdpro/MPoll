<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MPollViewTally extends JViewLegacy
{
	function display($tpl = null)
	{
		$jinput = JFactory::getApplication()->input;
		
		$this->qdata = $this->get('Questions');
		$this->pdata = $this->get('Poll');
		$this->polltitle = $this->get('PollTitle');

		// Set the toolbar
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
	}
	
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_MPOLL_MANAGER_TALLY'), 'MPoll');
		JToolbarHelper::link('index.php?option=com_mpoll&view=mpolls','Return to Polls','chevron-left');
	}
}
