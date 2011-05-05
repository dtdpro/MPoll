<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

$db =& JFactory::getDBO();
if ($this->showlist != 'never') {
	$jumpurl = 'index.php?option=com_mpoll&task='.$this->task.'&Itemid='.JRequest::getVar( 'Itemid' ).'&poll=';
	$jumplistt = JHTML::_('select.genericlist',$this->polllist,'chpoll','onchange="changePollT();"','poll_id','poll_name',$this->pdata['poll_id']);
	$jumplistb = JHTML::_('select.genericlist',$this->polllist,'chpoll','onchange="changePollB();"','poll_id','poll_name',$this->pdata['poll_id']);
	$jumpformt = '<form name ="chplft" action=""><p align="center">Select Poll: '.$jumplistt.'</p></form>';
	$jumpformb = '<form name ="chplfb" action=""><p align="center">Select Poll: '.$jumplistb.'</p></form>';
}

if ($this->task=='ballot') {  /*** DISPLAY POLL ***/
	if ($this->showlist == 'both' && ($this->listloc == 'top' || $this->listloc == 'both')) echo $jumpformt;
	echo '<div class="componentheading">'.$this->pdata['poll_name'].'</div>';
	$candopoll = true;
	if ($this->guest && $this->pdata['poll_regonly']) $candopoll=false;	
	if (!$candopoll) echo '<p align="left"><font color="#800000"><b>You must be logged in to participate</b></font></p>';
	echo '<p>'.$this->pdata['poll_desc'].'</p>';
	echo '<form name="evalf" method="post" action="" onSubmit="return checkRq();"><input type="hidden" name="stepnext" value="">';
	foreach ($this->qdata as $qdata) {
		if ($qdata['q_req'] && $qdata['q_type'] != 'mcbox') { 
			$req_q[] = 'q'.$qdata['q_id'];
			$req_t[] = $qdata['q_type'];
		}
		//Question #
		echo '<p>';
	
		//Question text if not a single checkbox
		if ($qdata['q_type'] != 'cbox') {
			echo '<strong>';
			echo $qdata['q_text'];
			echo '</strong>';
		}
		
		//output checkbox
		if ($qdata['q_type'] == 'cbox') { 
			echo '<label><input type="checkbox" size="40" name="q'.$qdata['q_id'].'">'.$qdata['q_text'].'</label><br />';
		}
		
		//verification msg area
		echo '<div id="'.'q'.$qdata['q_id'].'_msg" class="error_msg"></div>';
		
		//output radio select
		if ($qdata['q_type'] == 'multi') {
			$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qdata['q_id'].' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$qopts = $db->loadAssocList();
			$numopts=0;
			foreach ($qopts as $opts) {
				echo '<label><input type="radio" name="q'.$qdata['q_id'].'" value="'.$opts['opt_id'].'"';
				//if ($opts['opt_other']) echo " onfocus=\"dispOther('q".$qdata['q_id']."other".$opts['opt_id']."');\"";
				echo '> '.$opts['opt_txt'].'</label>';
				if ($opts['opt_other']) {
					echo ' <div id="q'.$qdata['q_id'].'other'.$opts['opt_id'].'" style="display: inline;" >';
					echo '<input type="text" size="30" onfocus="document.evalf.q'.$qdata['q_id'].'['.($numopts).'].checked=true;" name="q'.$qdata['q_id'].'o"></div>';
				}
				echo '<br />';
				$numopts++;
			}
		} 
	
		//output multi checkbox
		if ($qdata['q_type'] == 'mcbox') {
			//echo '<em>(check all that apply)</em><br />';
			$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qdata['q_id'].' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$qopts = $db->loadAssocList();
			$numopts=0;
			foreach ($qopts as $opts) {
				echo '<label><input type="checkbox" name="q'.$qdata['q_id'].'[]" value="'.$opts['opt_id'].'"';
				//if ($opts['opt_other']) echo " onchange=\"dispOther('q".$qdata['q_id']."other');\"";
				echo '> '.$opts['opt_txt'].'</label>';
				if ($opts['opt_other']) {
					echo ' <div id="q'.$qdata['q_id'].'other" style="display: inline;">';
					echo '<input type="text" size="30" name="q'.$qdata['q_id'].'o"></div>';
				}
				echo '<br />';
				$numopts++;
			}
		} 
		
		//output dropdown
		if ($qdata['q_type'] == 'dropdown') {
			//echo '<em>(check all that apply)</em><br />';
			$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qdata['q_id'].' ORDER BY ordering ASC';
			$db->setQuery( $query );
			$qopts = $db->loadAssocList();
			$options = '';
			$hasother=false;
			foreach ($qopts as $opts) {
				$options .= '<option value="'.$opts['opt_id'].'"';
				$options .= '>'.$opts['opt_txt'].'</option>';
				if ($opts['opt_other']) $hasother=true;
			}
			echo '<select name="q'.$qdata['q_id'].'">';
			echo $options;
			echo '</select>';
			//if ($hasother) echo ' <div id="q'.$qdata['q_id'].'other" style="display: none;"><input type="text" size="30" name="q'.$qdata['q_id'].'o"></div>';
			echo '<br />';
		} 
		
		
		//output text field
		if ($qdata['q_type'] == 'textbox') { echo '<input type="text" size="40" name="q'.$qdata['q_id'].'"><br>'; }
		
		//output text box
		if ($qdata['q_type'] == 'textar') { echo '<textarea cols="60" rows="3" name="q'.$qdata['q_id'].'"></textarea><br>'; }

		//add in verification if nedded
		if ($qdata['q_req'] && $qdata['q_type'] != 'mcbox') { $req_o[] = $numopts;}
		echo '</p>';
		
	}
	
	if ($candopoll) {
	echo '<p align="center">';
	echo '<input type="hidden" name="casting" value="true">';
	echo '<input name="castvote" id="castvote" value="Submit"  type="image" src="components/com_mpoll/images/submit.png" >';
	echo '</form></p>';
	} else { echo '<p align="center"><font color="#800000">You must be logged in to participate</font></p>'; }
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
	if ($this->showlist == 'both' && ($this->listloc == 'bottom' || $this->listloc == 'both')) echo $jumpformb;
} else if ($this->task=='results') { /*** DISPLAY POLL RESULTS ***/
	echo '<script type="text/javascript" src="http://www.google.com/jsapi"></script>';
	if (($this->showlist == 'both' || $this->showlist == 'after') && ($this->listloc == 'top' || $this->listloc == 'both')) echo $jumpformt;
	echo '<div class="componentheading">'.$this->pdata['poll_name'].'</div>';
	echo '<p>'.$this->pdata['poll_rmsg'].'</p>';
	if ($this->pdata['poll_showresults']) {
	foreach ($this->qdata as $qdata) {
		?> <style> .chartheader {display: none} .chartcell {width: 50%;border-bottom:dotted 1px #CCCCCC;} </style> <?php
		switch ($qdata['q_type']) {
		case 'multi':
		case 'mcbox':
		case 'dropdown':
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
			if ($qdata['q_charttype'] == 'bar') {
				foreach ($qopts as $opts) {
					if ($numr != 0) $per = $opts['COUNT(r.res_ans)']/$numr; else $per=1;
					echo '<tr bgcolor="'.$cbg.'"><td valign="center" align="left" width="200">'.$opts['opt_txt'].'</td><td valign="center" wdith="350"><img src="components/com_mpoll/images/bar_'.$barc.'.jpg" height="15" width="'.($per*300).'" align="absmiddle"> <b>'.$opts['COUNT(r.res_ans)'].'</b></td></tr>';
					$barc = $barc + 1;
					if ($barc==5) $barc=1;
					if ($cbg == "#FFFFFF") $cbg="#DDDDDD";
					else $cbg="#FFFFFF";
				}
			} else if ($qdata['q_charttype'] == 'pieg') {
				?>
				<script type="text/javascript">
				
				  google.load('visualization', '1', {'packages':['piechart']});
				  
				  google.setOnLoadCallback(drawChart<?php echo $qdata['q_id']; ?>);
				  
				  function drawChart<?php echo $qdata['q_id']; ?>() {
			
					var data = new google.visualization.DataTable();
					data.addColumn('string', 'Answer');
					data.addColumn('number', 'Count');
					data.addRows([
					  <?php 
					  $first=true;
					  foreach ($qopts as $opts) {
						  if (!$first) echo ",";
						  else $first = false;
						  echo "['".$opts['opt_txt']."', ".$opts['COUNT(r.res_ans)']."]";
					  }
					  ?>
					]);
			
					var chart<?php echo $qdata['q_id']; ?> = new google.visualization.PieChart(document.getElementById('chart_div<?php echo $qdata['q_id']; ?>'));
					chart<?php echo $qdata['q_id']; ?>.draw(data, {height: 250, is3D: true, legend: 'label',legendFontSize: 11});
				  }
				</script>
				<div id="chart_div<?php echo $qdata['q_id']; ?>"></div>
				 <?php
			} else if ($qdata['q_charttype'] == 'barg') {
				?>
				<script type="text/javascript">
    				google.load('visualization', '1', {packages: ['table']});
    				google.setOnLoadCallback(drawVisualization<?php echo $qdata['q_id']; ?>);
      				function drawVisualization<?php echo $qdata['q_id']; ?>() {
						var data = new google.visualization.DataTable();
  						data.addColumn('string', '');
  						data.addColumn('number', '');
  						data.addRows(<?php echo count($qopts); ?>);
  						<?php
						$count = 0;
						foreach ($qopts as $opts) {
								  echo 'data.setCell('.$count.',0,"'.$opts['opt_txt'].'");'."\n";
								  echo 'data.setCell('.$count.',1,'.$opts['COUNT(r.res_ans)'].');'."\n";
								  $count++;
					
						}
						?>
						var cssClassNames<?php echo $qdata['q_id']; ?> = {headerRow: 'chartheader', tableCell: 'chartcell'};
						var table<?php echo $qdata['q_id']; ?> = new google.visualization.Table(document.getElementById('chart_div<?php echo $qdata['q_id']; ?>'));
						var formatter<?php echo $qdata['q_id']; ?> = new google.visualization.TableBarFormat({width: 200, max: <?php echo $numr; ?>});
						formatter<?php echo $qdata['q_id']; ?>.format(data, 1); // Apply formatter to second column
						table<?php echo $qdata['q_id']; ?>.draw(data, {allowHtml: true, showRowNumber: false, cssClassNames: cssClassNames<?php echo $qdata['q_id']; ?>});
					}
				</script>
				<div id="chart_div<?php echo $qdata['q_id']; ?>"  ></div>
	 			<?php
			}
			//echo '<tr bgcolor="'.$cbg.'"><td align="right">Total:</td><td align="left"><b>';
			//if ($numr != 0) echo $numr; else echo '0';
			//echo '</b></td></tr>';
			echo '</table>';
			break;
		}
	}}
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
function changePollT() {
	var pollchg = 	document.chplft.chpoll.value;
	window.location = "<?php echo $jumpurl; ?>" + pollchg;
}
function changePollB() {
	var pollchg = 	document.chplfb.chpoll.value;
	window.location = "<?php echo $jumpurl; ?>" + pollchg;
}
</script>
<?php } ?>

