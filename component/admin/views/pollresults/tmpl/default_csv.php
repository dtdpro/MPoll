<?php defined('_JEXEC') or die('Restricted access'); 
$db =& JFactory::getDBO();

jimport( 'joomla.filesystem.file' );

$filename  =  'MPoll_Report' . '-' . date("Y-m-d_H:i:s").'.csv';
$contents = '';

$contents .= "\"Who\",\"EMail\",\"Completed On\",\"Status\"";
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
	$contents .= "\"".$i->cm_time."\",";
	$contents .= "\"".$i->cm_status."\"";

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
    	if ($qu->q_type == 'textar') {
		    $textar = '';
    		$textar = stripcslashes($i->$fn);
		    $chr_map = array(
			    // Windows codepage 1252
			    "\xC2\x82" => "'", // U+0082⇒U+201A single low-9 quotation mark
			    "\xC2\x84" => '""', // U+0084⇒U+201E double low-9 quotation mark
			    "\xC2\x8B" => "'", // U+008B⇒U+2039 single left-pointing angle quotation mark
			    "\xC2\x91" => "'", // U+0091⇒U+2018 left single quotation mark
			    "\xC2\x92" => "'", // U+0092⇒U+2019 right single quotation mark
			    "\xC2\x93" => '""', // U+0093⇒U+201C left double quotation mark
			    "\xC2\x94" => '""', // U+0094⇒U+201D right double quotation mark
			    "\xC2\x9B" => "'", // U+009B⇒U+203A single right-pointing angle quotation mark

			    // Regular Unicode     // U+0022 quotation mark (")
			    // U+0027 apostrophe     (')
			    "\xC2\xAB"     => '""', // U+00AB left-pointing double angle quotation mark
			    "\xC2\xBB"     => '""', // U+00BB right-pointing double angle quotation mark
			    "\xE2\x80\x98" => "'", // U+2018 left single quotation mark
			    "\xE2\x80\x99" => "'", // U+2019 right single quotation mark
			    "\xE2\x80\x9A" => "'", // U+201A single low-9 quotation mark
			    "\xE2\x80\x9B" => "'", // U+201B single high-reversed-9 quotation mark
			    "\xE2\x80\x9C" => '""', // U+201C left double quotation mark
			    "\xE2\x80\x9D" => '""', // U+201D right double quotation mark
			    "\xE2\x80\x9E" => '""', // U+201E double low-9 quotation mark
			    "\xE2\x80\x9F" => '""', // U+201F double high-reversed-9 quotation mark
			    "\xE2\x80\xB9" => "'", // U+2039 single left-pointing angle quotation mark
			    "\xE2\x80\xBA" => "'", // U+203A single right-pointing angle quotation mark
		    );
		    $chr = array_keys  ($chr_map); // but: for efficiency you should
		    $rpl = array_values($chr_map); // pre-calculate these two arrays
		    $textar = str_replace($chr, $rpl, html_entity_decode($textar, ENT_QUOTES, "UTF-8"));
    		$contents .= $textar;
    	}
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

