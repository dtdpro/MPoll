<?php defined('_JEXEC') or die('Restricted access'); 
$db =& JFactory::getDBO();

jimport( 'joomla.filesystem.file' );

$path = JPATH_SITE.'/cache/';
$filename  =  'MPoll_Report' . '-' . date("Y-m-d").'.csv';
$contents = '';

$contents .= "\"Who\",\"EMail\",\"Completed On\"";
foreach ($this->questions as $qu) {
	$contents .= ",\"".$qu->ordering." ".$qu->q_text."\"";
}
$contents .= "\n";
foreach ($this->items as $i)
{
	$contents .= "\"";
	if ($i->cm_user == 0) $contents .= 'Guest';
	else $contents .= $this->users[$i->cm_user]->name; 
	$contents .= "\",";
	$contents .= "\"".$this->users[$i->cm_user]->email."\",";
	$contents .= "\"".$i->cm_time."\"";

    foreach ($this->questions as $qu) {
    	$fn='q_'.$qu->q_id;
    	$contents .= ",\"";
		$qnum = 'q'.$qu->q_id.'ans';
    	if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown') {
    		$contents .= $this->options[$i->$fn];
    	}
    	if ($qu->q_type == 'textbox') { $contents .= $i->$fn; }
    	if ($qu->q_type == 'textar') { $contents .= $i->$fn; }
    	if ($qu->q_type == 'attach') {
    		if (strpos($i->$fn,"ERROR:") === FALSE && $i->$fn != "") {
    			$contents .= $i->$fn;
    		} else {
    			$contents .= $i->$fn;
    		}
    	}
    	if ($qu->q_type == 'email') { $contents .= $i->$fn; }
    	if ($qu->q_type == 'cbox') { if ($i->$fn) $contents .= 'Yes'; else $contents .= 'No'; }
    	if ($qu->q_type == 'mcbox') {
    		foreach ($i->$fn as $o) {
    			$contents .= $this->options[$o].' ';
    		}
    	}
		$contents .= "\"";
	}
	
	$contents .= "\n";
}
JFile::write($path.$filename,$contents);

$app = JFactory::getApplication();
$app->redirect('../cache/'.$filename);

