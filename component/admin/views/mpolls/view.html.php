<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MPollViewMPolls extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	
	function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
	
		// Set the submenu
		MPollHelper::addSubmenu(JRequest::getVar('view'));
	
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
	
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}
	
	protected function addToolBar() 
	{
		$state	= $this->get('State');
		$canDo = MPollHelper::getActions();
		JToolBarHelper::title(JText::_('COM_MPOLL_MANAGER_POLLS'), 'MPoll');
		if ($canDo->get('core.create')) 
		{
			JToolBarHelper::addNew('mpoll.add', 'JTOOLBAR_NEW');
			JToolBarHelper::custom('mpolls.copy', 'copy.png', 'copy_f2.png','JTOOLBAR_COPY', true);
		}
		if ($canDo->get('core.edit')) 
		{
			JToolBarHelper::editList('mpoll.edit', 'JTOOLBAR_EDIT');
			JToolBarHelper::divider();
			JToolBarHelper::custom('mpolls.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('mpolls.unpublish', 'unpublish.png', 'unpublish_f2.png','JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('mpolls.archive');
		}
		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'mpolls.delete', 'JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('mpolls.trash');
			JToolBarHelper::divider();
		}
		if ($canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_mpoll');
		}
		
		JHtmlSidebar::setAction('index.php?option=com_mows&view=products');
		
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_PUBLISHED'),'filter_state',JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true));
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_ACCESS'),'filter_access',JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access')));
		JHtmlSidebar::addFilter(JText::_('JOPTION_SELECT_CATEGORY'),'filter_category_id',JHtml::_('select.options', JHtml::_('category.options', 'com_mpoll'), 'value', 'text', $this->state->get('filter.category_id')));
	}
	
	protected function getSortFields()
	{
		return array(
				'p.published' => JText::_('JSTATUS'),
				'p.poll_created' => JText::_('COM_MPOLL_MPOLL_HEADING_ADDED'),
				'p.poll_modified' => JText::_('COM_MPOLL_MPOLL_HEADING_MODIFIED'),
				'p.poll_name' => JText::_('COM_MPOLL_MPOLL_HEADING_TITLE'),
				'p.access' => JText::_('JGRID_HEADING_ACCESS'),
				'p.poll_id' => JText::_('JGRID_HEADING_ID')
		);
	}


}
