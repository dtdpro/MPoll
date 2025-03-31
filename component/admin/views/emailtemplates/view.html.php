<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

use Joomla\CMS\MVC\View\GenericDataException;

class MPollViewEmailtemplates extends JViewLegacy
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

        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
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
		JToolBarHelper::title('Email Templates', 'MPoll');
		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('emailtemplate.add', 'JTOOLBAR_NEW');
			JToolBarHelper::custom('emailtemplates.copy', 'copy.png', 'copy_f2.png','JTOOLBAR_COPY', true);
		}
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::custom('emailtemplates.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('emailtemplates.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
		}
		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'emailtemplates.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('emailtemplates.trash');
			JToolBarHelper::divider();
		}
		JToolbarHelper::link('index.php?option=com_mpoll&view=mpolls','Return to Polls','chevron-left');
	}
	
	protected function getSortFields()
	{
		return array(
				'q.ordering'     => JText::_('JGRID_HEADING_ORDERING')
		);
	}
}
