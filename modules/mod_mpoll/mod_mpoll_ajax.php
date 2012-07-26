<?php
/*
# ESPRate Plugin for Joomla! 1.5.x - Version 0.1b
# License: http://www.gnu.org/copyleft/gpl.html
# Authors: Mike Amundsen
# Copyright (c) 2009 Corona Productions
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) . '/../..' );
define('JPATH_CORE', JPATH_BASE . '/../..');
define( 'DS', DIRECTORY_SEPARATOR );

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

$mainframe =& JFactory::getApplication('site');
$cfg =& JFactory::getConfig();
$db  =& JFactory::getDBO();
$user = &JFactory::getUser();

$pollid  = JRequest::getVar('poll');
$userid = $user->id;

//save completed

$pquery = 'SELECT * FROM #__mpoll_polls WHERE poll_id = '.$pollid.' && published=1';
$db->setQuery( $pquery );
$pdata = $db->loadAssoc();

$qc = 'INSERT INTO #__mpoll_completed (cm_user,cm_poll) VALUES ('.$userid.','.$pollid.')';
$db->setQuery( $qc );
$db->query();
$lastid = $db->insertid();

$query = 'SELECT * FROM #__mpoll_questions WHERE published = 1 && q_poll = '.$pollid;
$db->setQuery( $query );
$qdata = $db->loadAssocList(); 
foreach ($qdata as $ques) {
	if ($ques['q_type'] != 'mcbox') {
		$ans = JRequest::getVar('q'.$ques['q_id']);
		$q = 'INSERT INTO #__mpoll_results	(res_user,res_poll,res_qid,res_ans,res_cm) VALUES ("'.$userid.'","'.$pollid.'","'.$ques['q_id'].'","'.$ans.'","'.$lastid.'")';
		$db->setQuery( $q );
		$db->query();
	} else {
		$ansarr = JRequest::getVar('q'.$ques['q_id']);  //print_r($ansarr);
		if ($ansarr) $ans = implode(' ',$ansarr);
		else $ans = "";
		$q = 'INSERT INTO #__mpoll_results	(res_user,res_poll,res_qid,res_ans,res_cm) VALUES ("'.$userid.'","'.$pollid.'","'.$ques['q_id'].'","'.$ans.'","'.$lastid.'")';
		$db->setQuery( $q );
		$db->query();
	}
	
}
if ($pdata['poll_results_msg_before']) echo $pdata['poll_results_msg_before'];
//show results
$query = 'SELECT * FROM #__mpoll_questions WHERE published = 1 && q_poll = '.$pollid;
$db->setQuery( $query );
$qdata = $db->loadObjectList();

if ($pdata['poll_showresults']) {
	foreach ($qdata as $q) {
		echo '<div class="mpollmod-question">';
		$anscor=false;
		echo '<div class="mpollmod-question-text">'.$q->q_text.'</div>';
		switch ($q->q_type) {
			case 'multi':
				$qnum = 'SELECT count(res_qid) FROM #__mpoll_results WHERE res_qid = '.$q->q_id.' GROUP BY res_qid';
				$db->setQuery( $qnum );
				$qnums = $db->loadAssoc();
				$numr=$qnums['count(res_qid)'];
				$query  = 'SELECT o.* FROM #__mpoll_questions_opts as o ';
				$query .= 'WHERE o.opt_qid = '.$q->q_id.' ORDER BY ordering ASC';
				$db->setQuery( $query );
				$qopts = $db->loadObjectList();
				$tph=0;
				foreach ($qopts as &$o) {
					$qa = 'SELECT count(*) FROM #__mpoll_results WHERE res_qid = '.$q->q_id.' && res_ans = '.$o->opt_id.' GROUP BY res_ans';
					$db->setQuery($qa);
					$o->anscount = $db->loadResult();
					if ($o->anscount == "") $o->anscount = 0;
				}
				$gper=0; $ansper=0; $gperid = 0;
				foreach ($qopts as $opts) {
					if ($numr != 0) $per = ($opts->anscount+$opts->prehits)/($numr+$tph); else $per=1;
					if ($qans == $opts->id && $opts->correct) {
						$anscor=true;
					}
					echo '<div class="mpollmod-opt">';
					
					echo '<div class="mpollmod-opt-text">';
					if ($opts->opt_correct) echo '<div class="mpollmod-opt-correct">'.$opts->opt_txt.'</div>';
					else echo $opts->opt_txt;
					echo '</div>';
					echo '<div class="mpollmod-opt-count">';
					echo ($opts->anscount);
					echo '</div>';
					echo '<div class="mpollmod-opt-bar-box"><div class="mpollmod-opt-bar-bar" style="background-color: '.$opts->opt_color.'; width:'.($per*100).'%"></div></div>';
					echo '</div>';
					if ($gper < $per) { $gper = $per; $gperid = $opts->id; }
					if ($qans==$opts->opt_id) {
						if ($qdata->q_expl) $expl=$qdata->q_expl;
						else $expl=$opts->opt_expl;
					}
				}
				break;
				
			}
		echo '</div>';
	}
}
if ($pdata['poll_results_msg_mod']) echo $pdata['poll_results_msg_mod'];

