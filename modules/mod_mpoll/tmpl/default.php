<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
$db =& JFactory::getDBO();
?>
<form name="mpollf<?php echo $pdata->poll_id; ?>">

<?php 
if ($showtitle) {
	echo '<div class="mpollmod-title">'.$pdata->poll_name.'</div>';
}
?>
	<div id="mpollmod<?php echo $pdata->poll_id; ?>" class="mpollmod-pollbody">
			<?php
				if ($status != 'closed' && $status != 'done') {
					foreach ($qdatap as $qdata) {
						if ($qdata->q_req && $qdata->q_type != 'mcbox') { 
							$req_q[] = 'q'.$qdata->q_id;
							$req_t[] = $qdata->q_type;
						}
						//Question #
						echo '<div class="mpollmod-question">';
					
						//Question text if not a single checkbox
						if ($qdata->q_type != 'cbox') {
							echo '<div class="mpollmod-question-text">'.$qdata->q_text.'</div>';
							
						}
						echo '<div class="mpollmod-answers">';
						//output checkbox
						if ($qdata->q_type == 'cbox') { 
							echo '<div class="mpollmod-answer checkbox"><input type="checkbox" size="40" name="q'.$qdata->q_id.'" id="q'.$qdata->q_id.'"><label for="q'.$qdata->q_id.'"> '.$qdata->q_text.'</label></div>';
						}
						
						//verification msg area
						echo '<div id="'.'q'.$qdata->q_id.'_msg" class="mpollmod-error_msg"></div>';
						
						//output radio select
						if ($qdata->q_type == 'multi') {
							$numopts=0;
							foreach ($qdata->options as $opts) {
								echo '<div class="mpollmod-answer radio"><input type="radio" name="q'.$qdata->q_id.'" value="'.$opts->value.'" id="q'.$qdata->q_id.$opts->value.'"';
								if ($opts->opt_disabled) echo " disabled";
								echo '> <label for="q'.$qdata->q_id.$opts->value.'">'.$opts->text.'</label></div>';
								$numopts++;
							}
						} 
					
						/* disabled for module
						//output multi checkbox
						if ($qdata->q_type == 'mcbox') {
							//echo '<em>(check all that apply)</em><br />';
							$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qdata->q_id.' ORDER BY opt_order ASC';
							$db->setQuery( $query );
							$qopts = $db->loadAssocList();
							foreach ($qopts as $opts) {
								echo '<label><input type="checkbox" name="q'.$qdata->q_id.'[]" value="'.$opts['opt_id'].'" id="q'.$qdata->q_id.'">'.$opts['opt_txt'].'</label><br>';
							}
						} */
						
						//output text field
						if ($qdata->q_type == 'textbox' || $qdata->q_type == 'email') { echo '<input type="text" size="20" style="width:100%" name="q'.$qdata->q_id.'" id="q'.$qdata->q_id.'"><br>'; }
						if ($qdata->q_type == 'textar') { echo '<textarea cols="20" rows="2" style="width:100%" name="q'.$qdata->q_id.'" id="q'.$qdata->q_id.'"></textarea><br>'; }
					
						
						//add in verification if nedded
						if ($qdata->q_req && $qdata->q_type != 'mcbox') { $req_o[] = $numopts;}
						echo '</div></div>';
					}
					echo '<p align="center">';
					if ($status == 'open') {
						echo '<a href="javascript:checkRq'.$pdata->poll_id.'();" class="button uk-button">Submit</a>';
					} else { 
						echo $pdata->poll_regreqmsg; 
					}
					if ($params->get( 'showresultslink', 0 )) {
						echo ' <a href="'.JRoute::_('index.php?option=com_mpoll&task=results&poll='.$pdata->poll_id).'" class="button uk-button">Results</a>';
					}
					echo '</p>';
					
					$cnt = count($req_q);
					?>
					<script type='text/javascript'>
					function checkRq<?php echo $pdata->poll_id; ?>() {
						ev = document.mpollf<?php echo $pdata->poll_id; ?>;
						erMsg = '<span style="color:#800000"><b>Answer is Required</b></span>';
						erMsgEml = '<span style="color:#800000"><b>A valid email address is required</b></span>';
						cks = false; errs = false;
					<?
					for ($i=0; $i<$cnt; $i++) {
						if ($req_t[$i] == 'textbox') { echo "	if(isEmpty".$pdata->poll_id."(ev.".$req_q[$i].", erMsg,'".$req_q[$i]."'+'_msg')) { errs=true; }\n"; }
						if ($req_t[$i] == 'email') { echo "	if(isEmpty".$pdata->poll_id."(ev.".$req_q[$i].", erMsg,'".$req_q[$i]."'+'_msg') || isNotEmail".$pdata->poll_id."(ev.".$req_q[$i].", erMsgEml,'".$req_q[$i]."'+'_msg')) { errs=true; }\n"; }
						if ($req_t[$i] == 'multi') { echo "	if(isNCheckedR".$pdata->poll_id."(ev.".$req_q[$i].", erMsg,".$req_o[$i].",'".$req_q[$i]."'+'_msg')) { errs=true; }\n"; }
						if ($req_t[$i] == 'cbox') { echo "	if(isChecked".$pdata->poll_id."(ev.".$req_q[$i].", erMsg,'".$req_q[$i]."'+'_msg')) { errs=true; }\n"; }
						
					} 
				
				?>
					if (!errs) MPollAJAX<?php echo $pdata->poll_id; ?>();
					}
					
					function isNotEmail<?php echo $pdata->poll_id; ?>(elem, helperMsg,msgl){

						var emailExp=/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/;
						if (!elem.value.match(emailExp)) { 
							elem.focus();
							document.getElementById(msgl).innerHTML = helperMsg; 
							return true;
						} 
						
						document.getElementById(msgl).innerHTML ='';
							return false;
					}

					function isEmpty<?php echo $pdata->poll_id; ?>(elem, helperMsg,msgl){
						if(elem.value.length == 0){
							document.getElementById(msgl).innerHTML = helperMsg;
							document.getElementById(msgl).style.display='block';
							elem.focus(); // set the focus to this input
							return true;
						}
						document.getElementById(msgl).innerHTML ='';
						document.getElementById(msgl).style.display='none';
							return false;
					}
					
					function isNCheckedR<?php echo $pdata->poll_id; ?>(elem, helperMsg,cnt,msgl){
						var isit = false;
						for (var i=0; i<cnt; i++) {
							if(elem[i].checked){ isit = true; }
						}
						if (isit == false) {
							document.getElementById(msgl).innerHTML = helperMsg;
							document.getElementById(msgl).style.display='block';
							elem[0].focus(); // set the focus to this input
							return true;
						}
						document.getElementById(msgl).innerHTML = '';
						document.getElementById(msgl).style.display='none';
							return false;
					}
					function isChecked<?php echo $pdata->poll_id; ?>(elem, helperMsg,msgl) {
						if (elem.checked) {
							document.getElementById(msgl).innerHTML = '';
							document.getElementById(msgl).style.display='none';
							return false;
						} else { 
							document.getElementById(msgl).innerHTML = helperMsg;
							document.getElementById(msgl).style.display='block';
							elem.focus(); // set the focus to this input
							return true; 
						}
					}
					function getCheckedValue<?php echo $pdata->poll_id; ?>(radioObj) {
						if(!radioObj)
							return "";
						var radioLength = radioObj.length;
						if(radioLength == undefined)
							if(radioObj.checked)
								return radioObj.value;
							else
								return "";
						for(var i = 0; i < radioLength; i++) {
							if(radioObj[i].checked) {
								return radioObj[i].value;
							}
						}
						return "";
					}
	
					function MPollAJAX<?php echo $pdata->poll_id; ?>(){
						var ajaxRequest;  // The variable that makes Ajax possible!
						
						try{
							// Opera 8.0+, Firefox, Safari
							ajaxRequest = new XMLHttpRequest();
						} catch (e){
							// Internet Explorer Browsers
							try{
								ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
							} catch (e) {
								try{
									ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
								} catch (e){
									// Something went wrong
									alert("Your browser broke!");
									return false;
								}
							}
						}
						// Create a function that will receive data sent from the server
						ajaxRequest.onreadystatechange = function(){
							if(ajaxRequest.readyState == 4){
								var ajaxDisplay = document.getElementById('mpollmod<?php echo $pdata->poll_id; ?>');
								ajaxDisplay.innerHTML = ajaxRequest.responseText;
							}
						}
						var queryString = "?";
						<?php
						echo "queryString += 'poll=".$pdata->poll_id."&showresults=".$params->get( 'showresults', 1 )."&showresultslink=".$params->get( 'showresultslink', 0 )."&resultsas=".$params->get( 'resultsas', "count" )."';\n";
						if ($params->get( 'showresultslink', 0 )) echo "queryString += '&resultslink=".urlencode(JRoute::_('index.php?option=com_mpoll&task=results&poll='.$pdata->poll_id))."';\n";
						foreach ($qdatap as $qdata) {
							if ($qdata->q_type == 'multi') {
								echo "\t\t\t\t\tqueryString += '&q".$qdata->q_id."=' + getCheckedValue".$pdata->poll_id."(ev.q".$qdata->q_id.");\n";
							}
							else echo "\t\t\t\t\tqueryString += '&q".$qdata->q_id."=' + encodeURIComponent(ev.q".$qdata->q_id.".value);\n";
						} 
						?> 
						ajaxRequest.open("GET", "modules/mod_mpoll/mod_mpoll_ajax.php" + queryString, true);
						ajaxRequest.send(null); 
					}
					</script><?php 
				} else if ($status == 'done') {
					if ($pdata->poll_results_msg_before) echo $pdata->poll_results_msg_before;
					if ($pdata->poll_showresults && $params->get( 'showresults', 1 )) {
						foreach ($qdatap as $q) {
							if ($q->q_type == "multi") {
								echo '<div class="mpollmod-question">';
								$anscor=false;
								echo '<div class="mpollmod-question-text">'.$q->q_text.'</div>';
								echo '<div class="mpollmod-options">';
								switch ($q->q_type) {
									case 'multi':
										$qnum = 'SELECT count(res_qid) FROM #__mpoll_results WHERE res_qid = '.$q->q_id.' GROUP BY res_qid';
										$db->setQuery( $qnum );
										$qnums = $db->loadAssoc();
										$numr=$qnums['count(res_qid)'];
										$tph=0;
										foreach ($q->options as &$o) {
											$qa = 'SELECT count(*) FROM #__mpoll_results WHERE res_qid = '.$q->q_id.' && res_ans = '.$o->value.' GROUP BY res_ans';
											$db->setQuery($qa);
											$o->anscount = $db->loadResult();
											if ($o->anscount == "") $o->anscount = 0;
										}
										$gper=0; $ansper=0; $gperid = 0;
										foreach ($q->options as $opts) {
											if ($numr != 0) $per = ($opts->anscount+$opts->prehits)/($numr+$tph); else $per=1;
											echo '<div class="mpollmod-opt">';
								
											echo '<div class="mpollmod-opt-text">';
											if ($opts->opt_correct) echo '<div class="mpollmod-opt-correct">'.$opts->text.'</div>';
											else echo $opts->text;
											echo '</div>';
											echo '<div class="mpollmod-opt-count">';
											if ($params->get( 'resultsas', "count" ) == "count") {
												echo ($opts->anscount);
											} else {
												echo (int)($per*100)."%";
											}
											echo '</div>';
											echo '<div class="mpollmod-opt-bar-box"><div class="mpollmod-opt-bar-bar" style="background-color: '.$opts->opt_color.'; width:'.($per*100).'%"></div></div>';
											echo '</div>';
											if ($gper < $per) {
												$gper = $per; $gperid = $opts->value;
											}
											}
											break;
												
											}
												
											echo '</div></div>';
							}
							
						}
					}
					if ($pdata->poll_results_msg_mod) echo $pdata->poll_results_msg_mod;
					
					if ($params->get( 'showresultslink', 0 )) {
						echo '<p align="center"><a href="'.JRoute::_('index.php?option=com_mpoll&task=results&poll=').'" class="button uk-button">Results</a></p>';
					}
				}
				
				
				
				?>
				
		</div>
	

</form>
