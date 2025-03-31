<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;


class MPollControllerMPolls extends JControllerAdmin
{

	
	public function getModel($name = 'MPoll', $prefix = 'MPollModel', $config = [])
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	protected function allowAdd($data = array())
	{
		$user = JFactory::getUser();
		return ($user->authorise('core.create', $this->option) || count($user->getAuthorisedCategories($this->option, 'core.create')));
	}
	
	function copy()
	{
		// Check for request forgeries
		$this->checkToken();

		$jinput = JFactory::getApplication()->input;
	
		// Access check.
		if (!$this->allowAdd())
		{
			// Set the internal error and also the redirect error.
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');
		
			$this->setRedirect(
					JRoute::_(
							'index.php?option=' . $this->option . '&view=' . $this->view_list
							. $this->getRedirectToListAppend(), false
					)
			);
		
			return false;
		}
		
		// Get items to remove from the request.
		$cid = $jinput->get('cid', array(), 'array');
	
		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
				
			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);
				
			// Remove the items.
			if ($model->copy($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_COPIED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}
	
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}
	
	function questions()
	{
		// Check for request forgeries
		$this->checkToken();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$context = "com_mpoll.questions";
		// Get items to remove from the request.
		$ids = JFactory::getApplication()->input->get('cid', array(), 'array');
		if (!is_array($ids) || count($ids) < 1)
		{
			JError::raiseWarning(500, JText::_('COM_MPOLL_POLL_NO_ITEM_SELECTED'));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=mpolls', false));
		}
		else
		{
			$app->setUserState($context . '.poll',$ids[0]);
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=questions', false));
	}
	
	function pollresults()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$context = "com_mpoll.questions";
		// Get items to remove from the request.
		$ids = JFactory::getApplication()->input->get('cid', array(), 'array');
		if (!is_array($ids) || count($ids) < 1)
		{
			JError::raiseWarning(500, JText::_('COM_MPOLL_POLL_NO_ITEM_SELECTED'));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=mpolls', false));
		}
		else
		{
			$app->setUserState($context . '.poll',$ids[0]);
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=pollresults', false));
	}
	
	function tally()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$context = "com_mpoll.questions";
		// Get items to remove from the request.
		$ids = JFactory::getApplication()->input->get('cid', array(), 'array');
		if (!is_array($ids) || count($ids) < 1)
		{
			JError::raiseWarning(500, JText::_('COM_MPOLL_POLL_NO_ITEM_SELECTED'));
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=mpolls', false));
		}
		else
		{
			$app->setUserState($context . '.poll',$ids[0]);
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=tally', false));
	}

    function emailtemplates()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $context = "com_mpoll.emailtemplates";
        // Get items to remove from the request.
        $ids = JFactory::getApplication()->input->get('cid', array(), 'array');
        if (!is_array($ids) || count($ids) < 1)
        {
            JError::raiseWarning(500, JText::_('COM_MPOLL_POLL_NO_ITEM_SELECTED'));
            $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=mpolls', false));
        }
        else
        {
            $app->setUserState($context . '.poll',$ids[0]);
        }
        $this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=emailtemplates', false));
    }
	
}
