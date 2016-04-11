<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

class MPollController extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false)
	{
		// set default view if not set
		JRequest::setVar('view', JRequest::getCmd('view', 'mpolls'));

		// call parent behavior
		parent::display($cachable,$urlparams);

		// Set the submenu
		MPollHelper::addSubmenu('Polls');
	}

}
?>
