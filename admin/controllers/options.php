<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');


class MPollControllerOptions extends JControllerAdmin
{

	protected $text_prefix = "COM_MPOLL_OPTION";
	
	public function getModel($name = 'Option', $prefix = 'MPollModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}
