<?php
defined('_JEXEC') or die('Restricted access');

// Load CSV Exporter
require JPATH_COMPONENT."/vendor/autoload.php";

// Filename
$filename  =  'MPoll_Report' . '-' . date("Y-m-d_H:i:s").'.csv';

// Basic Headings
$headers = ["User","User Email","Completed On","Status"];

// Question Headings
foreach ($this->questions as $qu) {
	$headers[] = addslashes($qu->ordering).': '.addslashes($qu->q_text);
}

// Data Rows
$dataRows = [];

// Iterate responses
foreach ($this->items as $i)
{
	$dataRow = [];
	if ($i->cm_user == 0) {
		$dataRow[] = 'Guest';
		$dataRow[] = 'N/A';
	}
	else {
		$dataRow[] = $this->users[$i->cm_user]->name;
		$dataRow[] = $this->users[$i->cm_user]->email;
	}
	$dataRow[] = $i->cm_time;
	$dataRow[] = $i->cm_status;

    foreach ($this->questions as $qu) {
    	$fn='q_'.$qu->q_id;
        $fno='q_'.$qu->q_id.'_other';
		$qnum = 'q'.$qu->q_id.'ans';
        $answer = "";
	    if (property_exists($i,$fn)) {
		    if ( $qu->q_type == 'multi' || $qu->q_type == 'dropdown' ) {
			    if ( isset( $this->options[ $i->$fn ] ) ) {
				    $answer .= $this->options[ $i->$fn ];
			    }
			    if ( property_exists( $i, $fno ) ) {
				    $answer .= ': ' . $i->$fno;
			    }
		    }
		    if ( $qu->q_type == 'textbox' || $qu->q_type == 'mailchimp' || $qu->q_type == 'textar' || $qu->q_type == 'email' || $qu->q_type == 'datedropdown') {
			    $answer = $i->$fn;
		    }
			if ( $qu->q_type == 'attach' ) {
			    if ( strpos( $i->$fn, "ERROR:" ) === false && $i->$fn != "" ) {
				    $answer .= $i->$fn;
			    } else {
				    $answer .= $i->$fn;
			    }
		    }
		    if ( $qu->q_type == 'cbox' ) {
			    if ( $i->$fn ) {
                    $answer = 'Yes';
			    } else {
                    $answer = 'No';
			    }
		    }
		    if ( $qu->q_type == 'mcbox' || $qu->q_type == "mlist" ) {
			    $i->$fn = explode( " ", $i->$fn );
			    foreach ( $i->$fn as $o ) {
				    $answer .= $this->options[ $o ] . ' ';
			    }
                if ( property_exists($i,$fno)) {
                    $answer .= 'Other: ' . $i->$fno;
                }
		    }
	    }
        $dataRow[] = $answer;
	}
	$dataRows[] = $dataRow;
}

// HTTP Headers
$app = JFactory::getApplication();
$app->clearHeaders();
$app->setHeader( "Pragma", "public" );
$app->setHeader( 'Cache-Control', 'no-cache, must-revalidate', true );
$app->setHeader( 'Expires', 'Sat, 26 Jul 1997 05:00:00 GMT', true );
$app->setHeader( 'Content-Type', 'text/csv', true );
$app->setHeader( 'Content-Description', 'File Transfer', true );
$app->setHeader( 'Content-Disposition', 'attachment; filename="' . $filename . '"', true );
$app->setHeader( 'Content-Transfer-Encoding', 'binary', true );
$app->sendHeaders();

// Create CSV Writer
$csv = \League\Csv\Writer::createFromString();

// insert the Headings
$csv->insertOne($headers);

// insert all the records
$csv->insertAll($dataRows);

// CSV content
echo $csv->toString();

// stop
$app->close();


