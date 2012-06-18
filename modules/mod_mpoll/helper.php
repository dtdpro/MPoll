<?php
/**
* @version		$Id: helper.php 10381 2008-06-01 03:35:53Z pasamio $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modMPollHelper
{
	function getPoll($pollid)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid.' && published=1';
		$db->setQuery( $query );
		$pdata = $db->loadAssoc();
		return $pdata;
	}

	function getQuestions($pollid)
	{
		$db =& JFactory::getDBO();
		$query = 'SELECT * FROM #__mpoll_questions ';
		$query .= 'WHERE published = 1 && q_poll = '.$pollid.' ORDER BY ordering ASC';
		$db->setQuery( $query );
		$qdata = $db->loadObjectList();
		return $qdata;
	}
	function getCasted($pollid) {
		$db =& JFactory::getDBO();
		//$sewn = JFactory::getSession();
		//$sessionid = $sewn->getId();
		$user =& JFactory::getUser();
		$userid = $user->id;
		$query = 'SELECT * FROM #__mpoll_results WHERE res_user="'.$userid.'" && res_poll="'.$pollid.'"';
		$db->setQuery($query);
		$data = $db->loadAssoc();
		if (count($data) > 0) return true;
		else return false;
	}
}
?>
