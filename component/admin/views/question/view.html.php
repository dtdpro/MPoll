<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MPollViewQuestion extends JViewLegacy
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

	protected function addToolBar() 
	{
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->q_id == 0;
		$canDo = MPollHelper::getActions($this->item->q_id);
		JToolBarHelper::title($isNew ? JText::_('COM_MPOLL_MANAGER_QUESTION_NEW') : JText::_('COM_MPOLL_MANAGER_QUESTION_EDIT'), 'mpoll');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('question.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('question.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('question.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('question.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				JToolBarHelper::apply('question.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('question.save', 'JTOOLBAR_SAVE');
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('question.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			JToolBarHelper::cancel('question.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
