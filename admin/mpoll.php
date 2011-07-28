<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_mpoll')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// require helper file
JLoader::register('MPollHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'mpoll.php');


$document = JFactory::getDocument();
$document->addStyleDeclaration('.icon-48-MPoll {background-image: url(../media/com_mpoll/images/mpoll-48x48.png);}');

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by vidrev
$controller = JController::getInstance('MPoll');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();


?>
