<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

use Joomla\Utilities\ArrayHelper;

class MPollControllerPollResults extends JControllerAdmin
{
	function __construct()
	{
		parent::__construct();
	}

	public function delete()
	{
		// Check for request forgeries
		$this->checkToken();

		$jinput = JFactory::getApplication()->input;

		// Get items to remove from the request.
		$cid = $jinput->get('cid', array(), 'array');
		
		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_('No Records Selected'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('pollresults');

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(count($cid).' Record(s) Deleted');
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}
		// Invoke the postDelete method to allow for the child class to access the model.
		$this->postDeleteHook($model, $cid);

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	public function publish()
	{
		// Check for request forgeries
		$this->checkToken();

		$jinput = JFactory::getApplication()->input;

		// Get items to remove from the request.
		$cid = $jinput->get('cid', array(), 'array');
		$data  = ['publish' => 1, 'unpublish' => 0];
		$task  = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_('No Records Selected'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('pollresults');

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->publish($cid,$value))
			{
				$this->setMessage(count($cid).' Record(s) Published');
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}
		// Invoke the postDelete method to allow for the child class to access the model.
		$this->postDeleteHook($model, $cid);

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	//featured

	public function featured()
	{
		// Check for request forgeries
		$this->checkToken();

		$jinput = JFactory::getApplication()->input;

		// Get items to remove from the request.
		$cid = $jinput->get('cid', array(), 'array');
		$value = 1;

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_('No Records Selected'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('pollresults');

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->feature($cid,$value))
			{
				$this->setMessage(count($cid).' Record(s) Featured');
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}
		// Invoke the postDelete method to allow for the child class to access the model.
		$this->postDeleteHook($model, $cid);

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}

	//featured

	public function unfeatured()
	{
		// Check for request forgeries
		$this->checkToken();

		$jinput = JFactory::getApplication()->input;

		// Get items to remove from the request.
		$cid = $jinput->get('cid', array(), 'array');
		$value = 0;

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_('No Records Selected'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('pollresults');

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->feature($cid,$value))
			{
				$this->setMessage(count($cid).' Record(s) Featured');
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}
		// Invoke the postDelete method to allow for the child class to access the model.
		$this->postDeleteHook($model, $cid);

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
	}


}
?>
