<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

class MPollViewEmailtemplate extends JViewLegacy
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

        if ($this->item->tmpl_poll) {
            $model = $this->getModel();
            $this->questions = $model->getQuestions($this->item->tmpl_poll);
        }

        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	protected function addToolBar() 
	{
		$jinput = JFactory::getApplication()->input;
		$jinput->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->tmpl_id == 0;
		$canDo = MPollHelper::getActions($this->item->tmpl_id);
		JToolBarHelper::title($isNew ? 'MPoll: New Email Template' : "MPoll: Edit Email Template", 'mpoll');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('emailtemplate.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('emailtemplate.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('emailtemplate.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('emailtemplate.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				JToolBarHelper::apply('emailtemplate.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('emailtemplate.save', 'JTOOLBAR_SAVE');
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('emailtemplate.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			JToolBarHelper::cancel('emailtemplate.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
