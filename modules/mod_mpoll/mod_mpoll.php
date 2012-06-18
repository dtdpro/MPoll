<?php
/**
* @version		$Id: mod_poll.php 10381 2008-06-01 03:35:53Z pasamio $
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

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$tabclass_arr = array ('sectiontableentry2', 'sectiontableentry1');

$menu 	= &JSite::getMenu();
$items	= $menu->getItems('link', 'index.php?option=com_mpoll&task=ballot');

$doc = &JFactory::getDocument();
$doc->addStyleSheet('media/com_mpoll/mpoll.css');

$user =& JFactory::getUser();
$guest = $user->guest ? true : false;
		
$pdata   = modMPollHelper::getPoll($params->get( 'poll', 0 ));
$showtitle = $params->get( 'showtitle', 1 );
if ( $pdata && $pdata['poll_id'] ) {
	$status='open';
	//check if poll is still active
	if ((strtotime($pdata['poll_start']) > strtotime(date("Y-m-d H:i:s"))) && $pdata['poll_start'] != '0000-00-00 00:00:00') { $status='closed'; }
	if ((strtotime($pdata['poll_end']) < strtotime(date("Y-m-d H:i:s"))) && $pdata['poll_start'] != '0000-00-00 00:00:00') { $status='closed'; }
	
	//get questions
    $qdatap = modMPollHelper::getQuestions($params->get( 'poll', 0 ));
	
	//check if user has voted or not
	if ($pdata['poll_only']) {
			if (!$guest) $casted=modMPollHelper::getCasted($params->get( 'poll', 0 ));
			else $casted=false;
	} else {
			$casted=false;
	}
	if ($casted) $status='done';
	
	$layout = JModuleHelper::getLayoutPath('mod_mpoll');
    $tabcnt = 0;

	require($layout);
}