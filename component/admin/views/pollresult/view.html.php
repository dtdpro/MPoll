<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class MPollViewPollResult extends JViewLegacy
{
    public function display($tpl = null)
    {
        // get the Data
        $this->item = $this->get('Item');
        $model=$this->getModel();
        $this->questions		= $model->getQuestions($this->item->cm_poll,true);

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

        $isNew = $this->item->usr_user == 0;
        JToolBarHelper::title("Edit Submission");
        // Built the actions for new and existing records.
        if ($isNew)
        {
            // For new records, check the create permission.
            JToolBarHelper::apply('pollresult.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('pollresult.save', 'JTOOLBAR_SAVE');
            JToolBarHelper::cancel('pollresult.cancel', 'JTOOLBAR_CANCEL');
        }
        else
        {
            JToolBarHelper::apply('pollresult.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('pollresult.save', 'JTOOLBAR_SAVE');
            JToolBarHelper::cancel('pollresult.cancel', 'JTOOLBAR_CLOSE');
        }
    }
}