<?php

// No direct access to this file
defined('_JEXEC') or die;

abstract class MPollHelper
{
	public static function addSubmenu($submenu) 
	{
		JSubMenuHelper::addEntry(JText::_('COM_MPOLL_SUBMENU_POLLS'), 'index.php?option=com_mpoll', $submenu == 'Polls');
		JSubMenuHelper::addEntry(JText::_('COM_MPOLL_SUBMENU_CATEGORIES'), 'index.php?option=com_categories&view=categories&extension=com_mpoll', $submenu == 'categories');
		// set some global property
		$document = JFactory::getDocument();
		//$document->addStyleDeclaration('.icon-48-helloworld {background-image: url(../media/com_mpoll/images/tux-48x48.png);}');
		if ($submenu == 'categories') 
		{
			$document->setTitle(JText::_('COM_MPOLL_ADMINISTRATION_CATEGORIES'));
		}
	}
	/**
	 * Get the actions
	 */
	public static function getActions($vidId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($vidId)) {
			$assetName = 'com_mpoll';
		}
		else {
			$assetName = 'com_mpoll.poll.'.(int) $vidId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete', 'core.edit.state'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
}
