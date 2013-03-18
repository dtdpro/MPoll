<?php defined('_JEXEC') or die('Restricted access'); 
$order = JHTML::_('grid.order', $this->items);
$db =& JFactory::getDBO();
		
/*** DISPLAY POLL RESULTS ***/
	echo '<div class="componentheading">'.$this->pdata->poll_name.'</div>';
	foreach ($this->qdata as $q) {
		echo '<div class="mpollcom-question">';
		$anscor=false;
		echo '<div class="mpollcom-question-text">'.$q->q_text.'</div>';
		switch ($q->q_type) {
			case 'multi':
			case 'dropdown':
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
				foreach ($qopts as $opts) {
					if ($numr != 0) $per = ($opts->anscount+$opts->prehits)/($numr+$tph); else $per=1;
					echo '<div class="mpollcom-opt">';
					
					echo '<div class="mpollcom-opt-text">';
					if ($opts->opt_correct) echo '<span class="mpollcom-opt-correct"><b>'.$opts->opt_txt.'</b></span>';
					else echo $opts->opt_txt;
					echo '</div>';
					echo '<div class="mpollcom-opt-count">';
					echo ($opts->anscount);
					echo '</div>';
					echo '<div class="mpollcom-opt-bar-box"><div class="mpollcom-opt-bar-bar" style="background-color: '.$opts->opt_color.'; width:'.($per*100).'%"></div></div>';
					echo '</div>';
				}
				break;
				
			}
		echo '</div>';
	}
	
?>