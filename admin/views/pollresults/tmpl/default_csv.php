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
for ($i=0, $n=count( $this->items ); $i < $n; $i++)
{
	$row = &$this->items[$i];
	$contents .= "\"";
	if ($row['cm_user'] == 0) $contents .= 'Guest';
	else $contents .= $row['name']; 
	$contents .= "\",";
	$contents .= "\"".$row['email']."\",";
	$contents .= "\"".$row['cm_time']."\"";

    foreach ($this->questions as $qu) {
		$contents .= ",\"";
		$qnum = 'q'.$qu->q_id.'ans';
		if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown') { 
			if ($row[$qnum.'o']) $contents .= 'Other: '.$row[$qnum.'o'];
			else $contents .= $row[$qnum];
		}
		if ($qu->q_type == 'textbox') { $contents .= $row[$qnum]; }
		if ($qu->q_type == 'textar') { $contents .= $row[$qnum]; }
		if ($qu->q_type == 'email') { $contents .= $row[$qnum]; }
		if ($qu->q_type == 'cbox') { if ($row[$qnum] == 'on') $contents .= 'Checked'; else $contents .= 'Unchecked'; }
		if ($qu->q_type == 'mcbox') {
			$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qu->q_id.' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$qopts = $db->loadAssocList();
			$answers = explode(' ',$row[$qnum]);
			foreach ($qopts as $opts) {
				if (in_array($opts['opt_id'],$answers)) { 
					if ($opts['opt_other']) $contents .= '<em>Other:</em> '.$row[$qnum.'o'].'<br />';
					else $contents .= $opts['opt_txt'].'<br />'; 
				} 
			}
		}
		$contents .= "\"";
	}
	
	$contents .= "\n";
		
	$k = 1 - $k;
	$cq = $row->disporder+1;
}
JFile::write($path.$filename,$contents);

$app = JFactory::getApplication();
$app->redirect('../cache/'.$filename);

