<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
$db =& JFactory::getDBO();
?>
<script type="text/javascript">
	function MPollAJAX<?php echo $pdata->poll_id; ?>() {
		var url = '<?php echo JURI::base( true ); ?>/modules/mod_mpoll/mod_mpoll_ajax.php';
	    /* Send the data using post and put the results in a div */
	    jQuery.post( url, jQuery("#mpollf<?php echo $pdata->poll_id; ?>").serialize(),
	      function( data ) {
	          jQuery( "#mpollmod<?php echo $pdata->poll_id; ?>" ).empty().append( data );
	      }
	    );
	}

	jQuery(document).ready(function() {
		jQuery("#mpollf<?php echo $pdata->poll_id; ?>").validate({
			errorClass:"mf_error uk-form-danger",
			validClass:"mf_valid uk-form-success",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").parent("div").parent("div").next("div") );
		    },
		    submitHandler: function(form) { 
		    	MPollAJAX<?php echo $pdata->poll_id; ?>();
		    }
	    });

	});
</script>
<form name="mpollf<?php echo $pdata->poll_id; ?>" id="mpollf<?php echo $pdata->poll_id; ?>" action="">

<?php 
if ($showtitle) {
	echo '<div class="mpollmod-title">'.$pdata->poll_name.'</div>';
}
?>
	<div id="mpollmod<?php echo $pdata->poll_id; ?>" class="mpollmod-pollbody">

					

			<?php
				if ($status != 'closed' && $status != 'done') {
					
					foreach($qdatap as $f) {
				
						$sname = 'q_'.$f->q_id;
						if ($ri==1) $ri=0;
						else $ri=1;
						echo '<div class="mpoll-form-poll-row row-'.$sname.' mpoll-form-poll-row'.($ri % 2).'">';
						echo '<div class="mpoll-form-poll-label">';
						if ($f->q_req) echo "*";
						//field title
						if ($f->q_type != "cbox" && $f->q_type != "mailchimp") echo $f->q_text;
						echo '</div>';
						echo '<div class="mpoll-form-poll-value">';
						if ($f->q_type == "mcbox" || $f->q_type == "mlist") {
							if (!$f->q_min && !$f->q_max) echo '<em>(Select all that apply)</em><br />';
							if ($f->q_min && !$f->q_max) echo '<em>(Select at least '.$f->q_min.')</em><br />';
							if (!$f->q_min && $f->q_max) echo '<em>(Select at most '.$f->q_max.')</em><br />';
							if ($f->q_min && $f->q_max) echo '<em>(Select at least '.$f->q_min.' and at most '.$f->q_max.')</em><br />';
						}
					
						//checkbox
						if ($f->q_type=="cbox" || $f->q_type=="mailchimp") {
							echo '<div class="mform-radio">';
							echo '<div class="mform-radio-option checkbox">';
							if (!empty($f->value) && $f->q_type=="cbox") $checked = ($f->value == '1') ? ' checked="checked"' : '';
							else if ($f->params->mc_checked == "1") $checked = ' checked="checked"';
							else $checked = '';
							echo '<input type="checkbox" name="jform['.$sname.']" id="jform_'.$sname.'" class="mf_radio"';
							if ($f->q_req && $f->q_type=="cbox") { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
							echo $checked.'/>'."\n";
							echo '<label for="jform_'.$sname.'">';
							echo ' '.$f->q_text.'</label><br />'."\n";
							echo '</div></div>';
						}
					
						//multi checkbox
						if ($f->q_type=="mcbox") {
							echo '<div class="mform-radio">';
							$first = true;
							foreach ($f->options as $o) {
								if ($o->opt_selectable) {
									echo '<div class="mform-radio-option checkbox">';
									if (!empty($f->value)) $checked = in_array($o->value,$f->value) ? ' checked="checked"' : '';
									else $checked = '';
									echo '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->value.'" class="mf_radio" id="jform_'.$sname.$o->value.'"';
									if ($f->q_req && $first) {
										echo ' data-rule-required="true"';
										if ($f->q_min) echo ' data-rule-minlength="'.$f->q_min.'"';
										if ($f->q_max) echo ' data-rule-maxlength="'.$f->q_max.'"';
										echo ' data-msg-required="This Field is required"';
										if ($f->q_min) echo ' data-msg-minlength="Select at least '.$f->q_min.'"';
										if ($f->q_max) echo ' data-msg-maxlength="Select at most '.$f->q_max.'"';
										$first=false;
									}
									if ($o->opt_disabled) $checked .= ' disabled';
									echo $checked.'/>'."\n";
									echo '<label for="jform_'.$sname.$o->value.'">';
									echo ' '.$o->text.'</label></div>'."\n";
								} else {
									echo '<div class="mform-radio-noselect';
									echo ($first) ? ' mform-radio-noselecttop':'';
									echo '">'.$o->text.'</div>';
								}
									
							}
							echo '</div>';
						}
					
						//radio
						if ($f->q_type=="multi") {
							echo '<div class="mform-radio">';
							$first=true;
							foreach ($f->options as $o) {
								if ($o->opt_selectable) {
									echo '<div class="mform-radio-option radio">';
									if (!empty($f->value)) $checked = in_array($o->value,$f->value) ? ' checked="checked"' : '';
									else $checked = '';
									echo '<input type="radio" name="jform['.$sname.']" value="'.$o->value.'" id="jform_'.$sname.$o->value.'" class="mf_radio"';
									if ($f->q_req && $first) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; $first=false;}
									if ($o->opt_disabled) $checked .= ' disabled';
									echo $checked.'/>'."\n";
									echo '<label for="jform_'.$sname.$o->value.'">';
									echo ' '.$o->text;
									if ($o->opt_other) {
										echo ' <input type="text" value="'.$f->other.'" name="jform['.$sname.'_other]" id="jform_'.$sname.$o->value.'_other" class="mf_other">';
									}
									echo '</label>';
									echo '</div>'."\n";
								} else {
									echo '<div class="mform-radio-noselect';
									echo ($first) ? ' mform-radio-noselecttop':'';
									echo '">'.$o->text.'</div>';
								}
									
							}
							echo '</div>';
						}
					
						//dropdown
						if ($f->q_type=="dropdown") {
							echo '<div class="mform-field">';
							echo '<div class="mform-field-select">';
							echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="mf_field mf_select"';
							if ($f->q_req) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
							echo '>';
							foreach ($f->options as $o) {
								if (!empty($f->value)) $selected = in_array($o->value,$f->value) ? ' selected="selected"' : '';
								else $selected = '';
								if ($o->opt_disabled) $selected .= ' disabled';
								echo '<option value="'.$o->value.'"'.$selected.'>';
								echo ' '.$o->text.'</option>';
							}
							echo '</select>';
							echo '</div></div>';
						}
					
						//multilist
						if ($f->q_type=="mlist") {
							echo '<div class="mform-field">';
							echo '<div class="mform-field-select">';
							echo '<select id="jform_'.$sname.'" name="jform['.$sname.'][]" class="mf_field mf_mselect" size="4" multiple="multiple"';
							if ($f->q_req) {
								echo ' data-rule-required="true"';
								if ($f->q_min) echo ' data-rule-minlength="'.$f->q_min.'"';
								if ($f->q_max) echo ' data-rule-maxlength="'.$f->q_max.'"';
								echo ' data-msg-required="This Field is required"';
								if ($f->q_min) echo ' data-msg-minlength="Select at least '.$f->q_min.'"';
								if ($f->q_max) echo ' data-msg-maxlength="Select at most '.$f->q_max.'"';
								$first=false;
							}
							echo '>';
							foreach ($f->options as $o) {
								if (!empty($f->value)) $selected = in_array($o->value,$f->value) ? ' selected="selected"' : '';
								else $selected = '';
								if ($o->opt_disabled) $selected .= ' disabled';
								echo '<option value="'.$o->value.'"'.$selected.'>';
								echo ' '.$o->text.'</option>';
							}
							echo '</select>';
							echo '</div></div>';
						}
		
		
						//text field, phone #, email, username
						if ($f->q_type=="textbox" || $f->q_type=="email") {
							echo '<div class="mform-field">';
							echo '<div class="mform-field-text">';
							echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$f->value.'" class="mf_field" type="text"';
							if ($f->q_req) {
								echo ' data-rule-required="true"';
								if ($f->q_min) echo ' data-rule-minlength="'.$f->q_min.'"';
								if ($f->q_max) echo ' data-rule-maxlength="'.$f->q_max.'"';
								if ($f->q_type=="email") echo ' data-rule-email="true"';
								if ($f->q_match) echo ' data-rule-equalTo="#jform_'.$f->q_match.'"';
								echo ' data-msg-required="This Field is required"';
								if ($f->q_min) echo ' data-msg-minlength="Min length '.$f->q_min.' characters"';
								if ($f->q_max) echo ' data-msg-maxlength="Max length '.$f->q_max.' characters"';
								if ($f->q_type=="email") echo ' data-msg-email="Email address must be valid"';
								if ($f->q_match) echo ' data-msg-equalTo="Fields must match"';
							}
							echo '>';
							echo '</div></div>';
						}
					
						//text area
						if ($f->q_type=="textar") {
							echo '<div class="mform-field">';
							echo '<div class="mform-field-textarea">';
							echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="mf_field"';
							if ($f->q_req) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
							echo '>'.$f->value.'</textarea>';
							echo '</div></div>';
						}
		
						echo '</div>';
						echo '<div class="mpoll-form-poll-error">';
						echo '</div>';
						echo '</div>';
					}
					
					echo '<p align="center">';
					if ($status == 'open') {
						echo '<a href="javascript:submitMPoll'.$pdata->poll_id.'();" onclick="jQuery(\'#mpollf'.$pdata->poll_id.'\').submit()" class="button uk-button">Submit</a>';
					} else { 
						echo $pdata->poll_regreqmsg; 
					}
					if ($params->get( 'showresultslink', 0 )) {
						echo ' <a href="'.JRoute::_('index.php?option=com_mpoll&task=results&poll='.$pdata->poll_id).'" class="button uk-button">Results</a>';
					}
					echo '</p>';
					
					echo '<input type="hidden" name="poll" value="'.$pdata->poll_id.'">';
					echo '<input type="hidden" name="showresults" value="'.$params->get( 'showresults', 1 ).'">';
					echo '<input type="hidden" name="showresultslink" value="'.$params->get( 'showresultslink', 0 ).'">';
					echo '<input type="hidden" name="resultsas" value="'.$params->get( 'resultsas', "count" ).'">';
					echo JHtml::_('form.token');
					
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
