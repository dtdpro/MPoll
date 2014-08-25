<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once (JPATH_COMPONENT.'/controller.php');

// Require specific controller if requested
if($controller = JRequest::getVar('controller')) {
	require_once (JPATH_COMPONENT.'controllers/'.$controller.'.php');
}

// Load StyleSheet for template, based on config
$doc = &JFactory::getDocument();
$doc->addStyleSheet('media/com_mpoll/mpoll.css');

//jQuery
JHtml::_('jquery.framework');
$doc->addScript('media/com_mpoll/scripts/jquery.validate.js');
$doc->addScript('media/com_mpoll/scripts/additional-methods.js');
$doc->addScript('media/com_mpoll/scripts/jquery.simplemodal.js');

// Create the controller
$classname	= 'MPollController'.$controller;
$controller = new $classname( );
		
// Perform the Request task
$controller->execute( JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();
?>

