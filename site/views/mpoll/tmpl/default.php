<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

$db =& JFactory::getDBO();
if ($this->showlist != 'never') {
	$jumplist  = '<form name="jumppoll" action="" method="post">';
	$jumplist .= '<p align="center">Select Poll: ';
	$jumplist .= JHTML::_('select.genericlist',$this->polllist,'poll','onchange="changePoll();"','poll_id','poll_name',$this->pdata['poll_id']);
	$jumplist .= '<input type="hidden" name="jumptask" value="'.$this->task.'">';
	$jumplist .= '<input type="hidden" name="option" value="com_mpoll">';
	$jumplist .= '<input type="hidden" name="task" value="gotopoll">';
	$jumplist .= '</p>';
	$jumplist .= '</form>';
}

if ($this->task=='ballot') {  /*** DISPLAY POLL ***/
	if ($this->showlist == 'both') echo $jumplist;
	echo '<h2 class="componentheading">'.$this->pdata['poll_name'].'</h2>';
	echo '<p>'.$this->pdata['poll_desc'].'</p>';
	echo '<form name="evalf" method="post" action="" onSubmit="return checkRq();"><input type="hidden" name="stepnext" value="">';
	foreach ($this->qdata as $qdata) {
		if ($qdata->q_req && $qdata->q_type != 'mcbox') { 
			$req_q[] = 'q'.$qdata->q_id;
			$req_t[] = $qdata->q_type;
		}
		//Question #
		echo '<p>';
	
		//Question text if not a single checkbox
		if ($qdata->q_type != 'cbox') {
			echo '<strong>';
			echo $qdata->q_text;
			echo '</strong>';
		}
		
		//output checkbox
		if ($qdata->q_type == 'cbox') { 
			echo '<label><input type="checkbox" size="40" name="q'.$qdata->q_id.'">'.$qdata->q_text.'</label><br />';
		}
		
		//verification msg area
		echo '<div id="'.'q'.$qdata->q_id.'_msg" class="error_msg"></div>';
		
		//output radio select
		if ($qdata->q_type== 'multi') {
			$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qdata->q_id.' && published > 0 ORDER BY ordering ASC';
			$db->setQuery( $query );
			$qopts = $db->loadAssocList();
			$numopts=0;
			foreach ($qopts as $opts) {
				echo '<label><input type="radio" name="q'.$qdata->q_id.'" value="'.$opts['opt_id'].'"';
				//if ($opts['opt_other']) echo " onfocus=\"dispOther('q".$qdata->q_id']."other".$opts['opt_id']."');\"";
				echo '> '.$opts['opt_txt'].'</label>';
				if ($opts['opt_other']) {
					echo ' <div id="q'.$qdata->q_id.'other'.$opts['opt_id'].'" style="display: inline;" >';
					echo '<input type="text" size="30" onfocus="document.evalf.q'.$qdata->q_id.'['.($numopts).'].checked=true;" name="q'.$qdata->q_id.'o"></div>';
				}
				echo '<br />';
				$numopts++;
			}
		} 
	
		//output multi checkbox
		if ($qdata->q_type == 'mcbox') {
			//echo '<em>(check all that apply)</em><br />';
			$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qdata->q_id.' && published > 0 ORDER BY ordering ASC';
			$db->setQuery( $query );
			$qopts = $db->loadAssocList();
			$numopts=0;
			foreach ($qopts as $opts) {
				echo '<label><input type="checkbox" name="q'.$qdata->q_id.'[]" value="'.$opts['opt_id'].'"';
				//if ($opts['opt_other']) echo " onchange=\"dispOther('q".$qdata->q_id']."other');\"";
				echo '> '.$opts['opt_txt'].'</label>';
				if ($opts['opt_other']) {
					echo ' <div id="q'.$qdata->q_id.'other" style="display: inline;">';
					echo '<input type="text" size="30" name="q'.$qdata->q_id.'o"></div>';
				}
				echo '<br />';
				$numopts++;
			}
		} 
		
		//output dropdown
		if ($qdata->q_type == 'dropdown') {
			//echo '<em>(check all that apply)</em><br />';
			$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qdata->q_id.' && published > 0 ORDER BY ordering ASC';
			$db->setQuery( $query );
			$qopts = $db->loadAssocList();
			$options = '';
			$hasother=false;
			foreach ($qopts as $opts) {
				$options .= '<option value="'.$opts['opt_id'].'"';
				$options .= '>'.$opts['opt_txt'].'</option>';
				if ($opts['opt_other']) $hasother=true;
			}
			echo '<select name="q'.$qdata->q_id.'">';
			echo $options;
			echo '</select>';
			//if ($hasother) echo ' <div id="q'.$qdata->q_id.'other" style="display: none;"><input type="text" size="30" name="q'.$qdata->q_id.'o"></div>';
			echo '<br />';
		} 
		
		
		//output text field
		if ($qdata->q_type == 'textbox') { echo '<input type="text" size="40" name="q'.$qdata->q_id.'"><br>'; }
		
		//output text box
		if ($qdata->q_type == 'textar') { echo '<textarea cols="60" rows="3" name="q'.$qdata->q_id.'"></textarea><br>'; }

		//add in verification if nedded
		if ($qdata->q_req && $qdata->q_type != 'mcbox') { $req_o[] = $numopts;}
		echo '</p>';
		
	}
	
	echo '<p align="center">';
	echo '<input type="hidden" name="casting" value="true">';
	echo '<input name="castvote" id="castvote" value="Submit"  type="submit" class="button">';
	echo '</form></p>';
	$cnt = count($req_q);
	?>
	<script type='text/javascript'>
	function checkRq() {
		ev = document.evalf;
		erMsg = '<span style="color:#800000"><b>Answer is Required</b></span>';
		cks = false; errs = false;
	<?
	for ($i=0; $i<$cnt; $i++) {
		if ($req_t[$i] == 'textbox') { echo "	if(isEmpty(ev.".$req_q[$i].", erMsg,'".$req_q[$i]."'+'_msg')) { errs=true; }\n"; }
		if ($req_t[$i] == 'multi') { echo "	if(isNCheckedR(ev.".$req_q[$i].", erMsg,".$req_o[$i].",'".$req_q[$i]."'+'_msg')) { errs=true; }\n"; }
		if ($req_t[$i] == 'cbox') { echo "	if(isChecked(ev.".$req_q[$i].", erMsg,'".$req_q[$i]."'+'_msg')) { errs=true; }\n"; }
		
	} 
		echo "if (!errs) return true;\n";
		echo 'return false; }';
	?>
	
	function isEmpty(elem, helperMsg,msgl){
		if(elem.value.length == 0){
			document.getElementById(msgl).innerHTML = helperMsg;
			elem.focus(); // set the focus to this input
			return true;
		}
		document.getElementById(msgl).innerHTML ='';
			return false;
	}
	
	function isNCheckedR(elem, helperMsg,cnt,msgl){
		var isit = false;
		for (var i=0; i<cnt; i++) {
			if(elem[i].checked){ isit = true; }
		}
		if (isit == false) {
			document.getElementById(msgl).innerHTML = helperMsg;
			elem[0].focus(); // set the focus to this input
			return true;
		}
		document.getElementById(msgl).innerHTML = '';
			return false;
	}
	function isChecked(elem, helperMsg,msgl) {
		if (elem.checked) {
			document.getElementById(msgl).innerHTML = '';
			return false;
		} else { 
			document.getElementById(msgl).innerHTML = helperMsg;
			elem.focus(); // set the focus to this input
			return true; 
		}
	}
	
	function dispOther(field,opt) {
		document.getElementById(field).style.display='inline';
	}

	
	</script>
	<?php 


} else if ($this->task=='results') { /*** DISPLAY POLL RESULTS ***/
	if (($this->showlist == 'both' || $this->showlist == 'after')) echo $jumplist;
	echo '<h2 class="componentheading">'.$this->pdata['poll_name'].'</h2>';
	if ($this->pdata['poll_results_msg_before']) echo $this->pdata['poll_results_msg_before'];
	if ($this->pdata['poll_showresults']) {
		foreach ($this->qdata as $q) {
			echo '<div class="mpollcom-question">';
			$anscor=false;
			echo '<div class="mpollcom-question-text">'.$q->q_text.'</div>';
			switch ($q->q_type) {
				case 'multi':
				case 'mcbox':
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
						$qa = 'SELECT count(*) FROM #__mpoll_results WHERE res_qid = '.$q->q_id.' && res_ans LIKE "%'.$o->opt_id.'%" GROUP BY res_qid';
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
						echo '<div class="mpollcom-opt">';
						
						echo '<div class="mpollcom-opt-text">';
						if ($opts->opt_correct) echo '<div class="mpollcom-opt-correct">'.$opts->opt_txt.'</div>';
						else echo $opts->opt_txt;
						echo '</div>';
						echo '<div class="mpollcom-opt-count">';
						echo ($opts->anscount);
						echo '</div>';
						echo '<div class="mpollcom-opt-bar-box"><div class="mpollcom-opt-bar-bar" style="background-color: '.$opts->opt_color.'; width:'.($per*100).'%"></div></div>';
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
	if ($this->pdata['poll_results_msg_after']) echo $this->pdata['poll_results_msg_after'];
	if ($this->showstats) {
		echo '<p>';
		echo '<b>Number of Voters:</b> '.$this->ncast.'<br />';
		echo '<b>First Vote:</b> ';
		if ($this->ncast) echo date("l, F j, Y, g:i a",strtotime($this->fcast)).'<br />';
		else echo 'No Votes Yet<br />';
		echo '<b>Last Vote:</b> ';
		if ($this->ncast) echo date("l, F j, Y, g:i a",strtotime($this->lcast)).'<br />';
		else echo 'No Votes Yet<br />';
		echo '</p>';
	}
	if (($this->showlist == 'both' || $this->showlist == 'after') && ($this->listloc == 'bottom' || $this->listloc == 'both')) echo $jumpformb;
}


if ($this->showlist != 'never') {
?>
<script type='text/javascript'>
function changePoll() {
	document.jumppoll.submit();
}
</script>
<?php } ?>

