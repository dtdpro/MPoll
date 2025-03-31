<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

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
            throw new GenericDataException(implode("\n", $errors), 500);
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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->poll_id == 0;
		$canDo = MPollHelper::getActions($this->item->poll_id);
        $toolbar    = Toolbar::getInstance();

        ToolbarHelper::title(($isNew ? Text::_('COM_MPOLL_MANAGER_MPOLL_NEW') : Text::_('COM_MPOLL_MANAGER_MPOLL_EDIT')), 'pencil-alt article-add');

		if ($isNew)
		{
			if ($canDo->get('core.create'))
			{
                $toolbar->apply('mpoll.apply');
                $toolbar->save('mpoll.save');
                $toolbar->save2new('mpoll.save2new');

			}
            $toolbar->cancel('article.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
                $toolbar->apply('mpoll.apply');
                $toolbar->save('mpoll.save');
                if ($canDo->get('core.create')) {
                    $toolbar->save2new('mpoll.save2new');
                }
			}
            $toolbar->cancel('mpoll.cancel', 'JTOOLBAR_CLOSE');
		}
        $toolbar->divider();
        $toolbar->inlinehelp();
	}

}
