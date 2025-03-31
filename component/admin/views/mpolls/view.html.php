<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;

class MPollViewMPolls extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	
	function display($tpl = null)
	{
		$jinput = JFactory::getApplication()->input;

		$this->state		= $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
	
		if (count($errors = $this->get('Errors')))
		{
            throw new GenericDataException(implode("\n", $errors), 500);
		}
	
		$this->addToolBar();
		parent::display($tpl);
	}
	
	protected function addToolBar() 
	{
		$state	= $this->get('State');
		$canDo = MPollHelper::getActions();

        $toolbar = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('COM_MPOLL_MANAGER_POLLS'), 'MPoll');

        if ($canDo->get('core.create'))
        {
            $toolbar->addNew('mpoll.add');
            $toolbar->standardButton('copy', 'JTOOLBAR_COPY', 'mpolls.copy');
        }

        if ($canDo->get('core.edit'))
		{
            $toolbar->divider();
            $toolbar->publish('mpolls.publish')->listCheck(true);
            $toolbar->unpublish('mpolls.unpublish')->listCheck(true);
            $toolbar->archive('mpolls.archive')->listCheck(true);
		}
		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
            $toolbar->delete('mpolls.delete', 'JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
		} else if ($canDo->get('core.edit.state')) {
            $toolbar->trash('mpolls.trash')->listCheck(true);
		}
		if ($canDo->get('core.admin')) 
		{
            $toolbar->preferences('com_mpoll');
		}
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
