<?php defined('_JEXEC') or die('Restricted access'); 
$db =& JFactory::getDBO();

jimport( 'joomla.filesystem.file' );

$filename  =  'MPoll_Report' . '-' . date("Y-m-d_H:i:s").'.csv';
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
        $fno='q_'.$qu->q_id.'_other';
    	$contents .= ",\"";
		$qnum = 'q'.$qu->q_id.'ans';
    	if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown') {
    		$contents .= $this->options[$i->$fn];
			if ($i->$fno) { $contents .= ': '.$i->$fno; }
    	}
    	if ($qu->q_type == 'textbox' || $qu->q_type == 'mailchimp') { $contents .= $i->$fn; }
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
    	if ($qu->q_type == 'mcbox' || $qu->q_type=="mlist") {
    		$i->$fn = explode(" ",$i->$fn);
			foreach ($i->$fn as $o) {
    			$contents .= $this->options[$o].' ';
    		}
    	}
		$contents .= "\"";
	}
	
	$contents .= "\n";
}
JResponse::clearHeaders();
JResponse::setHeader("Pragma","public");
JResponse::setHeader('Cache-Control', 'no-cache, must-revalidate', true);
JResponse::setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT', true);
JResponse::setHeader('Content-Type', 'text/csv', true);
JResponse::setHeader('Content-Description', 'File Transfer', true);
JResponse::setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"', true);
JResponse::setHeader('Content-Transfer-Encoding', 'binary', true);
JResponse::sendHeaders();
echo $contents;
exit();

