<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MPollViewMPoll extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;
	
	public function display($tpl = null)
	{
		// get the Data
		$this->state = $this->get('State');
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		
		if ($this->item->poll_id) {
			$model = $this->getModel();
			$this->questions = $model->getQuestions($this->item->poll_id);
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
	
		// Set the toolbar
		$this->addToolBar();
	
		// Display the template
		parent::display($tpl);
	}
	

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->poll_id == 0;
		$canDo = MPollHelper::getActions($this->item->poll_id);
		JToolBarHelper::title($isNew ? JText::_('COM_MPOLL_MANAGER_MPOLL_NEW') : JText::_('COM_MPOLL_MANAGER_MPOLL_EDIT'), 'mpoll');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('mpoll.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('mpoll.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('mpoll.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('mpoll.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('mpoll.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('mpoll.save', 'JTOOLBAR_SAVE');

				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('mpoll.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				//JToolBarHelper::custom('mpoll.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('mpoll.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
