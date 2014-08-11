<?php

// No direct access to this file
defined('_JEXEC') or die;

abstract class MPollHelper
{
	public static function addSubmenu($submenu)
	{
		JHtmlSidebar::addEntry(JText::_('COM_MPOLL_SUBMENU_MPOLLS'), 'index.php?option=com_mpoll', $submenu == 'mpolls');
		JHtmlSidebar::addEntry(JText::_('COM_MPOLL_SUBMENU_CATEGORIES'), 'index.php?option=com_categories&extension=com_mpoll', $submenu == 'categories');
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
