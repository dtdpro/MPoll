<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MPollViewQuestions extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $polltitle;
	
	function display($tpl = null) 
	{
		$jinput = JFactory::getApplication()->input;

		// Get data from the model
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->polltitle = $this->get('PollTitle');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (JVersion::MAJOR_VERSION == 3) MPOLLHelper::addPollSubmenu($jinput->getVar('view'),$this->polltitle);
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		
		// Set the toolbar
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();

		// Display the template
		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		$state	= $this->state;
		$canDo = MPollHelper::getActions();
		JToolBarHelper::title(JText::_('COM_MPOLL_MANAGER_QUESTIONS'), 'MPoll');
		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('question.add', 'JTOOLBAR_NEW');
			JToolBarHelper::custom('questions.copy', 'copy.png', 'copy_f2.png','JTOOLBAR_COPY', true);
		}
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('question.edit', 'JTOOLBAR_EDIT');
			JToolBarHelper::divider();
			JToolBarHelper::custom('questions.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('questions.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
		}
		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'questions.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('questions.trash');
			JToolBarHelper::divider();
		}
		if (JVersion::MAJOR_VERSION == 4) JToolbarHelper::link('index.php?option=com_mpoll&view=mpolls','Return to Polls','chevron-left');
		
		
	}
	
	protected function getSortFields()
	{
		return array(
				'q.ordering'     => JText::_('JGRID_HEADING_ORDERING')
		);
	}
}
