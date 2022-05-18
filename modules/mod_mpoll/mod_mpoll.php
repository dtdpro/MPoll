<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).'/helper.php');

$tabclass_arr = array ('sectiontableentry2', 'sectiontableentry1');

$doc = JFactory::getDocument();
$doc->addStyleSheet('media/com_mpoll/mpoll.css');

JHtml::_('jquery.framework');
$doc->addScript('media/com_mpoll/scripts/jquery.validate.js');
$doc->addScript('media/com_mpoll/scripts/additional-methods.js');

// Load helper
require_once('components/com_mpoll/helpers/mpoll.php');

// Load Router
require_once('components/com_mpoll/router.php');

$cfg = MPollHelper::getConfig();

if ($cfg->load_uikit) {
	$doc->addStyleSheet('media/com_mpoll/uikit/uikit.'.$cfg->load_uikit.'.min.css');
	$doc->addScript('media/com_mpoll/uikit/uikit.min.js');
}

$user = JFactory::getUser();
		
$pdata   = modMPollHelper::getPoll($params->get( 'poll', 0 ));
$showtitle = $params->get( 'showtitle', 1 );
$showdesc = $params->get( 'showdesc', 0 );
if ( $pdata && $pdata->poll_id ) {
	$status='open';
	// Check if poll is still active
	if ((strtotime($pdata->poll_start) > strtotime(date("Y-m-d H:i:s"))) && $pdata->poll_start != '0000-00-00 00:00:00') { $status='closed'; }
	if ((strtotime($pdata->poll_end) < strtotime(date("Y-m-d H:i:s"))) && $pdata->poll_start != '0000-00-00 00:00:00') { $status='closed'; }
	
	// Get questions
    $qdata = modMPollHelper::getQuestions($params->get( 'poll', 0 ));
	
	// Check if user has voted or not
	if ($pdata->poll_only) {
			if ($user->id) $casted=modMPollHelper::getCasted($params->get( 'poll', 0 ));
			else $casted=false;
	} else {
			$casted=false;
	}
	if ($casted) $status='done';
	
	// Check if login requird
	if (!$user->id && $pdata->poll_regreq) { 
		$status="regreq"; 
	}
	
	// Check if ACL Met
	if ($user->id && !in_array($pdata->access,$user->getAuthorisedViewLevels())) {
		$status="accessreq";
	}

	// Load reCAPTCHA JS if enabled
	if ( $pdata->poll_recaptcha ) {
		$doc = JFactory::getDocument();
		if ($cfg->rc_theme == "v3") { // v3
			$doc->addScript( 'https://www.google.com/recaptcha/api.js?render=' . $cfg->rc_api_key );
		} else { // v2
			$doc->addScript( 'https://www.google.com/recaptcha/api.js' );
		}
	}
	
	
	$layout = JModuleHelper::getLayoutPath('mod_mpoll');
    $tabcnt = 0;

	require($layout);
}