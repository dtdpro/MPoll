<?php defined('_JEXEC') or die('Restricted access'); 
$order = JHTML::_('grid.order', $this->items);
$db =& JFactory::getDBO();
		
/*** DISPLAY POLL RESULTS ***/
	echo '<div class="componentheading">'.$this->pdata['poll_name'].'</div>';
	foreach ($this->qdata as $qdata) {
		switch ($qdata['q_type']) {
		case 'multi':
		case 'mcbox':
			echo '<p>';
			echo '<strong>';
			echo $qdata['q_text'];
			echo '</strong></p>';
			echo '<table width="100%" border="0">'; 
			$qnum = 'SELECT count(res_qid) FROM #__mpoll_results WHERE res_qid = '.$qdata['q_id'].' GROUP BY res_qid';
			$db->setQuery( $qnum );
			$qnums = $db->loadAssocList();
			$numr=$qnums[0]['count(res_qid)'];
			$query  = 'SELECT *,COUNT(r.res_ans) FROM #__mpoll_questions_opts as o ';
			$query .= 'LEFT JOIN #__mpoll_results as r ON o.opt_id = r.res_ans ';
			$query .= 'WHERE opt_qid = '.$qdata['q_id'].' GROUP BY o.opt_id ORDER BY o.ordering ASC';
			$db->setQuery( $query );
			$qopts = $db->loadAssocList();
			$barc=1;
			$cbg = "#FFFFFF";
			foreach ($qopts as $opts) {
				if ($numr != 0) $per = $opts['COUNT(r.res_ans)']/$numr; else $per=1;
				echo '<tr bgcolor="'.$cbg.'"><td valign="center" align="left" width="600">'.$opts['opt_txt'].'</td><td valign="center" wdith="350"><img src="../components/com_mpoll/images/bar_'.$barc.'.jpg" height="15" width="'.($per*300).'" align="absmiddle"> <b>'.$opts['COUNT(r.res_ans)'].'</b></td></tr>';
				$barc = $barc + 1;
				if ($barc==5) $barc=1;
				if ($cbg == "#FFFFFF") $cbg="#DDDDDD";
				else $cbg="#FFFFFF";
			}
			echo '</table>';
			break;
		}
	}
	
?>