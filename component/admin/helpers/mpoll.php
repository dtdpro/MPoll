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
	
	public static function addPollSubmenu($submenu,$polltitle)
	{
		JHtmlSidebar::addEntry(JText::_('COM_MPOLL_SUBMENU_MPOLLSRETURN'),'index.php?option=com_mpoll&view=mpolls',$submenu == 'mpolls');
		JHtmlSidebar::addEntry('<span class="nav-header">'.$polltitle.'</span>');
		JHtmlSidebar::addEntry(JText::_('COM_MPOLL_SUBMENU_QUESTIONS'),'index.php?option=com_mpoll&view=questions',$submenu == 'questions');
		JHtmlSidebar::addEntry(JText::_('COM_MPOLL_SUBMENU_RESULTS'),'index.php?option=com_mpoll&view=pollresults',$submenu == 'pollresults');
		JHtmlSidebar::addEntry(JText::_('COM_MPOLL_SUBMENU_TALLY'),'index.php?option=com_mpoll&view=tally',$submenu == 'tally');
	}
	
	public static function addQuestionSubmenu($submenu,$questiontitle)
	{
		JHtmlSidebar::addEntry(JText::_('COM_MPOLL_SUBMENU_QUESTIONSRETURN'),'index.php?option=com_mpoll&view=questions',$submenu == 'questions');
		JHtmlSidebar::addEntry('<span class="nav-header">'.$questiontitle.'</span>');
		JHtmlSidebar::addEntry(JText::_('COM_MPOLL_SUBMENU_OPTIONS'),'index.php?option=com_mpoll&view=options',$submenu == 'options');
	}

	function getConfig() {
		$menuConfig = JComponentHelper::getParams('com_mpoll');
		$mamscfg = $menuConfig->toObject();
		return $mamscfg;
	}

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
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete', 'core.edit.state', 'core.deleterecords'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
}
