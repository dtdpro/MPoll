<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

class MPollControllerPollResult extends JControllerForm
{
	protected $text_prefix = "COM_MPOLL_POLLRESULT";

    public function cancel($key = null)
    {
        $this->checkToken();

        // Initialise variables.
        $app		= JFactory::getApplication();
        $input      = JFactory::getApplication()->input;
        $model		= $this->getModel();
        $context	= "$this->option.edit.$this->context";

        if (empty($key)) {
            $key = 'cm_id';
        }

        $recordId	= $input->get($key);

        // Clean the session data and redirect.
        $this->releaseEditId($context, $recordId);
        $app->setUserState($context.'.data',	null);
        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

        return true;
    }


    public function edit($key = null, $urlVar = null)
    {
        // Initialise variables.
        $app		= JFactory::getApplication();
        $input      = JFactory::getApplication()->input;
        $model		= $this->getModel();
        $cid		= $input->get('cm_id', 0, 'get', 'int');
        $context	= "$this->option.edit.$this->context";
        $append		= '';

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = 'cm_id';
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = 'id';
        }
        // Get the previous record id (if any) and the current record id.
        $recordId	= (int) $cid;


        // Access check.
        /*if (!$this->allowEdit(array($key => $recordId), $key)) {
            $app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');
            $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

            return false;
        }*/

        $this->holdEditId($context, $recordId);
        $app->setUserState($context.'.data', null);
        $this->setRedirect('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, $urlVar));

        return true;
    }

    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        // Initialise variables.
        $app		= JFactory::getApplication();
        $lang		= JFactory::getLanguage();
        $input      = JFactory::getApplication()->input;
        $model		= $this->getModel();
        $data		= $input->get('jform', array(), 'post', 'array');
        $context	= "$this->option.edit.$this->context";
        $task		= $this->getTask();

        // set the name of the primary key for the data.
        $key = 'cm_id';


        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = 'id';
        }

        $recordId	= $input->get($urlVar);

        $session	= JFactory::getSession();
        $registry	= $session->get('registry');

        if (!$this->checkEditId($context, $recordId)) {
            // Somehow the person just went to the form and tried to save it. We don't allow that.
            $app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId), 'error');
            $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

            return false;
        }

        // Populate the row id from the session.
        $data[$key] = $recordId;

        // Attempt to save the data.
        if (!$model->save($data)) {
            // Save the data in the session.
            $app->setUserState($context.'.data', $data);

            // Redirect back to the edit screen.
            $app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
            $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, "id"), false));

            return false;
        }

        $this->setMessage(JText::_("Saved"));

        // Redirect the user and adjust session state based on the chosen task.
        switch ($task)
        {
            case 'apply':
                // Set the record data in the session.
                $this->holdEditId($context, $recordId);
                $app->setUserState($context.'.data', null);


                // Redirect back to the edit screen.
                $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.$this->getRedirectToItemAppend($recordId, "id"), false));
                break;

            default:
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context.'.data', null);

                // Redirect to the list screen.
                $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));
                break;
        }

        // Invoke the postSave method to allow for the child class to access the model.
        $this->postSaveHook($model, $data);

        return true;
    }


}
