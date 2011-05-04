<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class MPollControllerAnsQuest extends MPollController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}

	function csvme() {
		$filename       = 'MPoll_Report' . '-' . date("Y-m-d");
		$qid = JRequest::getVar('question');
		$model = $this->getModel('ansquest');
		$data = $model->getQInfo($qid);
		$qtype = $data->qtype;
		$items = $model->getResponses($qid,$qtype);
		$db =& JFactory::getDBO();

		JResponse::setHeader('Content-Type', 'application/octet-stream');
		JResponse::setHeader('Content-Disposition', 'attachment; filename="'. $filename . '.csv"');
		echo 'Question: '.$data->qtext."\n";
		echo "Name,Answered On,Answer\n";
		for ($i=0, $n=count( $items ); $i < $n; $i++)
		{
			$row = &$items[$i];
			echo $row->firstname.' '.$row->lastname.',';
			echo $row->anstime.',';
			if (qtype == 'multi') { 
				echo $row->opt_txt; 
			} else if ($qtype == 'mcbox') {
				$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$row->res_qid.' ORDER BY ordering ASC';
				$db->setQuery( $query );
				$qopts = $db->loadAssocList();
				$answers = explode(' ',$row->res_ans);
				foreach ($qopts as $opts) {
					if (in_array($opts['opt_id'],$answers)) { echo $opts['opt_txt'].'  '; } 
				}
			} else { echo $row->res_ans; }
			echo "\n";
		}


	}
}
?>
