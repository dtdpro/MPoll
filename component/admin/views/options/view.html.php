<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MPollViewOptions extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $polltitle;
	
	function display($tpl = null)
	{
		// Get data from the model
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->questiontitle = $this->get('QuestionTitle');
	
		MPOLLHelper::addQuestionSubmenu(JRequest::getVar('view'),$this->questiontitle);
	
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
		$state	= $this->get('State');
		$canDo = MPollHelper::getActions();
		JToolBarHelper::title(JText::_('COM_MPOLL_MANAGER_OPTIONS'), 'MPoll');
		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('option.add', 'JTOOLBAR_NEW');
			JToolBarHelper::custom('options.copy', 'copy.png', 'copy_f2.png','JTOOLBAR_COPY', true);
		}
		if ($canDo->get('core.edit'))
		{
			JToolBarHelper::editList('option.edit', 'JTOOLBAR_EDIT');
			JToolBarHelper::divider();
			JToolBarHelper::custom('options.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('options.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
		}
		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'options.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('options.trash');
			JToolBarHelper::divider();
		}
		JHtmlSidebar::setAction('index.php?option=com_mpoll&view=options');
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_PUBLISHED'),'filter_state',JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true));
	}
}
