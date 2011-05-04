<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class MPollControllerPollResults extends MPollController
{
	function __construct()
	{
		parent::__construct();
	}

	function csvme() {
		$filename       = 'Poll_Results_Detailed' . '-' . date("Y-m-d");
		$pid = JRequest::getVar('poll');
		$model = $this->getModel('pollresults');
		$questions = $model->getQuestions($pid);
		$items = $model->getResponses($pid,$questions);
		$db =& JFactory::getDBO();

		JResponse::setHeader('Content-Type', 'application/octet-stream');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="'. $filename . '.csv"');
		//echo 'Course: '.$data->qtext."\n";
		echo "Name,email,Timestamp";
		foreach ($questions as $qu) {
			echo ',#'.$qu->q_text;
		}
		echo "\n";
		for ($i=0, $n=count( $items ); $i < $n; $i++)
		{
			$row = &$items[$i];
			if ($row['cm_user'] == 0) { echo 'Guest,NoEmail'; }
			else echo $row['name'].','.$row['email']; 
			echo ',';
			echo $row['cm_time'].',';
			foreach ($questions as $qu) {
				
				$qnum = 'q'.$qu->q_id.'ans';
				if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown') { 
					if ($row[$qnum.'o']) echo 'Other: '.str_replace(',','',$row[$qnum.'o']);
					else echo str_replace(',','',$row[$qnum]);
				}
				if ($qu->q_type == 'textbox') { echo str_replace(',','',$row[$qnum]); }
				if ($qu->q_type == 'textar') { echo str_replace(',','',$row[$qnum]); }
				if ($qu->q_type == 'cbox') { if ($row[$qnum] == 'on') echo 'Checked'; else echo 'Unchecked'; }
				if ($qu->q_type == 'mcbox') {
					$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qu->q_id.' ORDER BY ordering ASC';
					$db->setQuery( $query );
					$qopts = $db->loadAssocList();
					$answers = explode(' ',$row[$qnum]);
					foreach ($qopts as $opts) {
						if (in_array($opts['opt_id'],$answers)) { 
							if ($opts['opt_other']) echo 'Other: '.str_replace(',','',$row[$qnum.'o']).' ';
							else echo str_replace(',','',$opts['opt_txt']).' '; 
						} 
					}
				}
				echo ',';
			}
		
			
			/*if (qtype == 'multi') { 
				if ($this->data->qcat=='assess') {
					if ($row->correct) echo $row->opttxt;
					else echo $row->opttxt; 
				}
				else echo $row->opttxt;
			} else if ($qtype == 'mcbox') {
				$query = 'SELECT * FROM #__ce_questions_opts WHERE question = '.$row->question.' ORDER BY disporder ASC';
				$db->setQuery( $query );
				$qopts = $db->loadAssocList();
				$answers = explode(' ',$row->answer);
				foreach ($qopts as $opts) {
					if (in_array($opts['id'],$answers)) { echo $opts['opttxt'].'  '; } 
				}
			} else { echo $row->answer; }*/
			echo "\n";
		}


	}
}
?>
