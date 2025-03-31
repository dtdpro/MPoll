<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class MPollViewPollResult extends JViewLegacy
{
    var $hasEmailField = false;

    public function display($tpl = null)
    {
        // get the Data
        $this->item = $this->get('Item');
        $model = $this->getModel();
        $this->questions = $model->getQuestions($this->item->cm_poll, true);
        $this->hasEmailField = $model->hasEmailField($this->item->cm_poll);
        $this->payUrl = $model->getPayUrl($this->item);

        switch ($this->_layout) {
            case 'edit':
                $this->payments = $model->getPayments($this->item->cm_id);
                $this->addToolBar();
                break;
            case 'createemail':
                $this->availableTemplates = $model->getAvailableTemplates($this->item->cm_poll);
                $this->addEmailToolBar();
                break;
        }

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }

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

        $isNew = $this->item->cm_id == 0;
        JToolBarHelper::title("Edit Submission");
        // Built the actions for new and existing records.
        if ($isNew)
        {
            JToolBarHelper::apply('pollresult.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('pollresult.save', 'JTOOLBAR_SAVE');
            JToolBarHelper::cancel('pollresult.cancel', 'JTOOLBAR_CANCEL');
        }
        else
        {
            JToolBarHelper::apply('pollresult.apply', 'JTOOLBAR_APPLY');
            JToolBarHelper::save('pollresult.save', 'JTOOLBAR_SAVE');
            JToolBarHelper::cancel('pollresult.cancel', 'JTOOLBAR_CLOSE');
            if ($this->hasEmailField) JToolBarHelper::addNew('pollresult.createemail','Create Email');
        }
    }

    /**
     * Setting the toolbar
     */
    protected function addEmailToolBar()
    {
        $jinput = JFactory::getApplication()->input;
        $jinput->set('hidemainmenu', true);
        JToolBarHelper::title("Create Email");
        JToolBarHelper::save('pollresult.sendEmail', 'Send Email');
        JToolBarHelper::cancel('pollresult.cancelemail', 'JTOOLBAR_CANCEL');
    }
}