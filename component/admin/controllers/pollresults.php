<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class MPollControllerPollResults extends JControllerAdmin
{
	function __construct()
	{
		parent::__construct();
	}

	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		
		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_('No Records Selected'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('pollresults');

			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

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
}
?>
