<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$db = JFactory::getDBO();
if ($params->get( 'submitvia', 'ajax' ) == 'ajax') {
?>
<script type="text/javascript">

	jQuery(document).ready(function() {
		var validator<?php echo $pdata->poll_id; ?> = jQuery("#mpollf<?php echo $pdata->poll_id; ?>").validate({
			errorClass:"uk-form-danger",
			validClass:"uk-form-success",
			errorElement: "div",
			errorPlacement: function(error, element) {
				error.appendTo( element.parent("div") );
				error.addClass("uk-alert uk-alert-danger uk-form-controls-text")
			},
            onsubmit: false
		});

        jQuery("#mpollf<?php echo $pdata->poll_id; ?>").submit(function( event ) {
            event.preventDefault();
            if (validator<?php echo $pdata->poll_id; ?>.form()) {
                if (typeof ga === 'function') {
                    ga('send', 'event', 'MPoll', 'submit', '<?php echo $pdata->poll_name; ?>');
                }
                if (typeof gtag === 'function') {
                    gtag('event', 'submit', { 'event_category' : 'MPoll', 'event_label' : '<?php echo $pdata->poll_name; ?>' });
                }
				<?php if ( $pdata->poll_recaptcha && $cfg->rc_theme == "v3") { ?>
                grecaptcha.ready(function() {
                    grecaptcha.execute('<?php echo $cfg->rc_api_key; ?>', {action: 'submit'}).then(function(token) {
                        jQuery('#reCapResponse<?php echo $pdata->poll_id; ?>').val(token);
                        var url = '<?php echo JURI::base( true ); ?>/modules/mod_mpoll/mod_mpoll_ajax.php';
                        var formData = new FormData(jQuery("#mpollf<?php echo $pdata->poll_id; ?>")[0]);
                        jQuery.ajax({
                            type: "POST",
                            url: url,
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function( data ) {
                                jQuery( "#mpollmod<?php echo $pdata->poll_id; ?>" ).empty().append( data );
                            },
                            error: function(errResponse) {
                                jQuery( "#mpollmod<?php echo $pdata->poll_id; ?>" ).append( errResponse );
                            }
                        });
                    });
                });
				<?php } else { ?>
                var url = '<?php echo JURI::base( true ); ?>/modules/mod_mpoll/mod_mpoll_ajax.php';
                var formData = new FormData(jQuery("#mpollf<?php echo $pdata->poll_id; ?>")[0]);
                jQuery.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function( data ) {
                        jQuery( "#mpollmod<?php echo $pdata->poll_id; ?>" ).empty().append( data );
                    },
                    error: function(errResponse) {
                        jQuery( "#mpollmod<?php echo $pdata->poll_id; ?>" ).append( errResponse );
                    }
                });
				<?php } ?>
            }
        });

	});

    function reCapChecked<?php echo $pdata->poll_id; ?>() {
        jQuery('#reCapChecked<?php echo $pdata->poll_id; ?>').val("checked");
    }
</script>
<form name="mpollf<?php echo $pdata->poll_id; ?>" id="mpollf<?php echo $pdata->poll_id; ?>" enctype="multipart/form-data" action="" class="uk-form">
<?php } else { ?>
    <script type="text/javascript">

        jQuery(document).ready(function() {
            var validator<?php echo $pdata->poll_id; ?> = jQuery("#mpollf<?php echo $pdata->poll_id; ?>").validate({
                errorClass:"uk-form-danger",
                validClass:"uk-form-success",
                errorElement: "div",
                errorPlacement: function(error, element) {
                    error.appendTo( element.parent("div") );
                    error.addClass("uk-alert uk-alert-danger uk-form-controls-text")
                },
                onsubmit: false
            });

            jQuery("#mpollf<?php echo $pdata->poll_id; ?>").submit(function( event ) {
                event.preventDefault();
                if (validator<?php echo $pdata->poll_id; ?>.form()) {
                    if (typeof ga === 'function') {
                        ga('send', 'event', 'MPoll', 'submit', '<?php echo $pdata->poll_name; ?>');
                    }
                    if (typeof gtag === 'function') {
                        gtag('event', 'submit', { 'event_category' : 'MPoll', 'event_label' : '<?php echo $pdata->poll_name; ?>' });
                    }
			        <?php if ( $pdata->poll_recaptcha && $cfg->rc_theme == "v3") { ?>
                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php echo $cfg->rc_api_key; ?>', {action: 'submit'}).then(function(token) {
                            jQuery('#reCapResponse<?php echo $pdata->poll_id; ?>').val(token);
                            jQuery("#mpollf<?php echo $pdata->poll_id; ?>")[0].submit();
                        });
                    });
			        <?php } else { ?>
                    jQuery("#mpollf<?php echo $pdata->poll_id; ?>")[0].submit();
			        <?php } ?>
                }
            });

        });

        function reCapChecked<?php echo $pdata->poll_id; ?>() {
            jQuery('#reCapChecked<?php echo $pdata->poll_id; ?>').val("checked");
        }
    </script>
    <form name="mpollf<?php echo $pdata->poll_id; ?>" id="mpollf<?php echo $pdata->poll_id; ?>" enctype="multipart/form-data" method="post" action="<?php echo JRoute::_('index.php?option=com_mpoll&view=mpoll&poll='.$pdata->poll_id.'&task=castvote&Itemid='.$params->get( 'menuitem', 101),false); ?>" class="uk-form">
<?php } ?>

	<?php
	if ($showtitle) {
		echo '<div class="mpollmod-title">'.$pdata->poll_name.'</div>';
	}

	if ($showdesc) {
		//Message before Questions
		echo $pdata->poll_desc;
	}
	?>
	<div id="mpollmod<?php echo $pdata->poll_id; ?>" class="mpollmod-pollbody"> <?php

		if ($status != 'closed' && $status != 'done') {

		    $ri=0;
			foreach($qdata as $f) {

				//Debug
				//echo '<pre>'; print_r($f); echo '</pre>';

				$sname = 'q_'.$f->q_id;
				if ($ri==1) $ri=0;
				else $ri=1;

				//Start Row
				echo '<div class="row-'.$sname.' mpoll-form-poll-row'.($ri % 2).' uk-form-row uk-margin-top">';

				//field title/label
				if ($f->q_type != "message" && $f->q_type != "header") {
					echo '<div class="uk-form-label uk-text-bold">';
					if ($f->q_req && $params->get( 'showreq', 1 )) echo "*";
					if ($f->q_type != "cbox" && $f->q_type != "message" && $f->q_type != "header") echo $f->q_text;
					echo '</div>';
				}

				//Start Field
				if ($f->q_type == "message") echo '<div class="uk-alert">';
				else if ($f->q_type == "header") echo '<div class="uk-margin-top uk-text-bold uk-text-large">';
				else {
					echo '<div class="uk-form-controls';
					if ($f->q_type != "cbox" || $f->q_pretext || $f->q_hint) echo ' uk-form-controls-text';
					echo '">';
				}

				//Pretext
				if ($f->q_pretext) echo '<div class="">'.$f->q_pretext.'</div>';

				//Limit warnings
				if ($f->q_type == "mcbox" || $f->q_type == "mlist") {
					if (!$f->q_min && !$f->q_max) echo '<em>(Select all that apply)</em><br />';
					if ($f->q_min && !$f->q_max) echo '<em>(Select at least '.$f->q_min.')</em><br />';
					if (!$f->q_min && $f->q_max) echo '<em>(Select at most '.$f->q_max.')</em><br />';
					if ($f->q_min && $f->q_max) echo '<em>(Select at least '.$f->q_min.' and at most '.$f->q_max.')</em><br />';
				}

				//Message
				if ($f->q_type == "message") echo $f->q_text;
				if ($f->q_type == "header") echo $f->q_text;


				//checkbox
				if ($f->q_type=="cbox") {
					if (!empty($f->value) && $f->q_type=="cbox") $checked = ($f->value == '1') ? ' checked="checked"' : '';
					else $checked = '';
					echo '<input type="checkbox" name="jform['.$sname.']" id="jform_'.$sname.'" class="uk-checkbox"';
					if ($f->q_req && $f->q_type=="cbox") { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
					echo $checked.'/>'."\n";
					echo '<label for="jform_'.$sname.'">';
					echo ' '.$f->q_text.'</label><br />'."\n";
				}

				//multi checkbox
				if ($f->q_type=="mcbox") {
					$first = true;
					foreach ($f->options as $o) {
						if ($o->opt_selectable) {
							if (!empty($f->value)) $checked = in_array($o->value,$f->value) ? ' checked="checked"' : '';
							else $checked = '';
							echo '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->value.'" class="uk-checkbox" id="jform_'.$sname.$o->value.'"';
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
							echo ' '.$o->text;
                            if ($o->opt_other) {
                                echo ' <input type="text" value="';
                                if (property_exists($f,'other')) echo $f->other;
                                echo '" name="jform['.$sname.'_other]" id="jform_'.$sname.$o->value.'_other" class="">';
                            }
                            echo '</label><br />'."\n";
						} else {
							echo '<span class="uk-text-bold">'.$o->text.'</span><br />';
						}

					}
				}

				//radio
				if ($f->q_type=="multi") {

					$first=true;
					foreach ($f->options as $o) {
						if ($o->opt_selectable) {
							if (!empty($f->value)) $checked = in_array($o->value,$f->value) ? ' checked="checked"' : '';
							else $checked = '';
							echo '<input type="radio" name="jform['.$sname.']" value="'.$o->value.'" id="jform_'.$sname.$o->value.'" class="uk-radio"';
							if ($f->q_req && $first) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; $first=false;}
							if ($o->opt_disabled) $checked .= ' disabled';
							echo $checked.'/>'."\n";
							echo '<label for="jform_'.$sname.$o->value.'">';
							echo ' '.$o->text;
							if ($o->opt_other) {
								echo ' <input type="text" value="';
                                if (property_exists($f,'other')) echo $f->other;
                                echo '" name="jform['.$sname.'_other]" id="jform_'.$sname.$o->value.'_other" class="">';
							}
							echo '</label>';
							echo '<br />'."\n";
						} else {
							echo '<span class="uk-text-bold">'.$o->text.'</span><br />';
						}

					}
				}

				//dropdown
				if ($f->q_type=="dropdown") {
					echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="uk-width-1-1 uk-select"';
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
				}

				//multilist
				if ($f->q_type=="mlist") {
					echo '<select id="jform_'.$sname.'" name="jform['.$sname.'][]" class="uk-width-1-1 uk-select" size="4" multiple="multiple"';
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
				}


				//text field, phone #, email, username
				if ($f->q_type=="textbox" || $f->q_type=="email") {
					echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="';
                    if( $f->value) echo $f->value;
                    echo '" class="mf_field uk-width-1-1 uk-input" type="text"';
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
				}

				//text area
				if ($f->q_type=="textar") {
					echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="mf_field uk-width-1-1 uk-textarea"';
					if ($f->q_req) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
					echo '>'.$f->value.'</textarea>';
				}

				//File Attachment
				if ($f->q_type == 'attach') {
					echo '<input name="q_'.$f->q_id.'[]" id="jform_'.$sname.'" type="file" size="40" multiple="multiple" class="mf_file"';
					if ($f->q_req) {
						echo ' data-rule-required="true"';
						echo ' data-msg-required="This Field is required"';
					}
					echo ' />';
				}

                // Date Dropdown
				if ( $f->q_type == 'datedropdown' ) {
                    $value = $f->value;
					$selected = ' selected="selected"';
					$html = "";
					$html .= '<select id="jform_' . $sname . '_month" name="jform[' . $sname . '_month]" class="form-control mf_field input-sm uk-select uk-width-1-3">';
					$html .= '<option value="01"'; $html .= (substr($value,0,2) == "01") ? $selected : ''; $html .= '>01 - January</option>';
					$html .= '<option value="02"'; $html .= (substr($value,0,2) == "02") ? $selected : ''; $html .= '>02 - February</option>';
					$html .= '<option value="03"'; $html .= (substr($value,0,2) == "03") ? $selected : ''; $html .= '>03 - March</option>';
					$html .= '<option value="04"'; $html .= (substr($value,0,2) == "04") ? $selected : ''; $html .= '>04 - April</option>';
					$html .= '<option value="05"'; $html .= (substr($value,0,2) == "05") ? $selected : ''; $html .= '>05 - May</option>';
					$html .= '<option value="06"'; $html .= (substr($value,0,2) == "06") ? $selected : ''; $html .= '>06 - June</option>';
					$html .= '<option value="07"'; $html .= (substr($value,0,2) == "07") ? $selected : ''; $html .= '>07 - July</option>';
					$html .= '<option value="08"'; $html .= (substr($value,0,2) == "08") ? $selected : ''; $html .= '>08 - August</option>';
					$html .= '<option value="09"'; $html .= (substr($value,0,2) == "09") ? $selected : ''; $html .= '>09 - September</option>';
					$html .= '<option value="10"'; $html .= (substr($value,0,2) == "10") ? $selected : ''; $html .= '>10 - October</option>';
					$html .= '<option value="11"'; $html .= (substr($value,0,2) == "11") ? $selected : ''; $html .= '>11 - November</option>';
					$html .= '<option value="12"'; $html .= (substr($value,0,2) == "12") ? $selected : ''; $html .= '>12 - December</option>';
					$html .= '</select>';

					$html .= '<select id="jform_'.$sname.'_day" name="jform[' . $sname . '_day]" class="form-control mf_field input-sm uk-select uk-width-1-3">';
					for ($i=1;$i<=31;$i++) {
						if ($i<10) $val = "0".$i;
						else $val=$i;
						$html .= '<option value="'.$val.'"';
						$html .= (substr($value,3,2) == $val) ? $selected : '';
						$html .= '>'.$val.'</option>';
					}
					$html .=  '</select>';

					$html .= '<select id="jform_'.$sname.'_year" name="jform[' . $sname . '_year]" class="form-control mf_field input-sm uk-select uk-width-1-3">';
					for ($i=$f->q_min;$i<=$f->q_max;$i++) {
						if ($i<10) $val = "0".$i;
						else $val=$i;
						$html .= '<option value="'.$val.'"';
						$html .= (substr($value,6,4) == $val) ? $selected : '';
						$html .= '>'.$val.'</option>';
					}
					$html .=  '</select>';

					echo $html;
				}

				if ($f->q_hint && $f->q_type!="captcha") echo '<div class="uk-text-small">'.$f->q_hint.'</div>';

				//End Field
				echo '</div>';

				//End Row
				echo '</div>';
			}


            if ($cfg->usehoneypot) {
                // Start Honeypot Row & Field
                echo '<div class="row-' . $sname . ' mpoll-form-poll-row-info uk-form-row uk-margin-top"><div class="uk-form-controls">';

                // Honepot Label
                echo '<div class="uk-form-label uk-text-bold">SPAM Check, leave blank</div>';

                // Honepot Input
                echo '<input autocomplete="off"  name="name" id="jform_name" value="" class="mf_field uk-width-1-1 uk-input" type="text">';

                // End Honeypot Row & Field
                echo '</div></div>';
            }

			//reCAPTCHA
			if ( $pdata->poll_recaptcha && $cfg->rc_theme != "v3" ) {
				echo '<div class="uk-form-row uk-margin-top mpoll-form-poll-row'.($ri % 2).'">';
				echo '<div class="uk-form-label">';
				echo '</div>';
				echo '<div class="uk-form-controls">';
				echo '<input type="hidden" id="reCapChecked" name="reCapChecked'.$pdata->poll_id.'" value="" data-rule-required="true" data-msg-required="reCaptcha Required">';
				echo '<div class="g-recaptcha" data-callback="reCapChecked'.$pdata->poll_id.'" data-theme="'.$cfg->rc_theme.'" data-sitekey="'.$cfg->rc_api_key.'"></div>';
				echo '</div></div>';
			} else if ($pdata->poll_recaptcha && $cfg->rc_theme == "v3") {
				echo '<input type="hidden" id="reCapResponse'.$pdata->poll_id.'" name="g-recaptcha-response" value="" class="ignore">';
			}

            //submitbuttonalign
            $buttonAlign = $params->get( 'submitbuttonalign', 'center' );
		    if ($buttonAlign == "center") {
			    echo '<p class="uk-text-center text-center">';
		    } else if ($buttonAlign == "left"){
				echo '<p class="uk-text-left text-left">';
            } else if ($buttonAlign == "right"){
			    echo '<p class="uk-text-right text-right">';
		    }

			if ($status == 'open') {
				$buttontext = "Submit";
				if ($pdata->poll_payment_enabled) $buttontext .= " & Pay";
				echo '<button type="submit" class="btn btn-'.$cfg->btncolor.' uk-button uk-button-'.$cfg->btncolor.'">'.$buttontext.'</button>';
			}

			if ($status == 'regreq') {
				echo '<div class="uk-alert uk-alert-warning">'.$pdata->poll_regreqmsg.'</div>';
			}

			if ($status == 'accessreq') {
				echo '<div class="uk-alert uk-alert-danger">'.$pdata->poll_accessreqmsg.'</div>';
			}

			if ($params->get( 'showresultslink', 0 )) {
				echo ' <a href="'.JRoute::_('index.php?option=com_mpoll&task=results&poll='.$pdata->poll_id).'" class="btn '.$cfg->btncolor.' uk-button '.$cfg->btncolor.'">Results</a>';
			}
			echo '</p>';

			echo '<input type="hidden" name="poll" value="'.$pdata->poll_id.'">';
			echo '<input type="hidden" name="showresults" value="'.$params->get( 'showresults', 1 ).'">';
			echo '<input type="hidden" name="showresultslink" value="'.$params->get( 'showresultslink', 0 ).'">';
			echo '<input type="hidden" name="resultslink" value="'.JRoute::_(JUri::root().'index.php?option=com_mpoll&task=results&poll='.$pdata->poll_id).'">';
			echo '<input type="hidden" name="paylink" value="'.JRoute::_(JUri::root().'index.php?option=com_mpoll&task=pay&poll='.$pdata->poll_id).'">';
			echo '<input type="hidden" name="resultsas" value="'.$params->get( 'resultsas', "count" ).'">';
			echo JHtml::_('form.token');

		} else if ($status == 'done') {
			if ($pdata->poll_results_msg_before) echo $pdata->poll_results_msg_before;
			if ($pdata->poll_showresults && $params->get( 'showresults', 1 )) {
				foreach ($qdata as $qr) {
					if ($qr->q_type == "mcbox" || $qr->q_type == "multi" || $qr->q_type == "dropdown" || $qr->q_type == "mlist") {
						echo '<div class="mpollmod-question">';
						$anscor=false;
						echo '<div class="mpollmod-question-text">'.$qr->q_text.'</div>';
						switch ($qr->q_type) {
							case 'multi':
							case 'mcbox':
							case 'mlist':
							case 'dropdown':
								$numr=0;
								foreach ($qr->options as &$o) {
									$qa = $db->getQuery(true);
									$qa->select('count(*)');
									$qa->from('#__mpoll_results');
									$qa->where('res_qid = '.$qr->q_id);
									$qa->where('res_ans LIKE "%'.$o->value.'%"');
									$qa->group('res_qid');
									$db->setQuery($qa);
									$o->anscount = (int)$db->loadResult();
									$numr = $numr + $o->anscount;
								}
								foreach ($qr->options as $opts) {
									if ($opts->opt_selectable) {
										if ($numr != 0) $per = ($opts->anscount)/($numr); else $per=1;
										echo '<div class="mpollmod-opt">';

										echo '<div class="mpollmod-opt-text">';
										if ($opts->opt_correct) echo '<div class="mpollmod-opt-correct">'.$opts->text.'</div>';
										else echo $opts->text;
										echo '</div>';
										echo '<div class="mpollmod-opt-count">';
										if ($resultsas == "percent") echo (int)($per*100)."%";
										else echo ($opts->anscount);
										echo '</div>';
										echo '<div class="mpollmod-opt-bar-box"><div class="mpollmod-opt-bar-bar" style="background-color: '.$opts->opt_color.'; width:'.($per*100).'%"></div></div>';
										echo '</div>';
									}
								}
								break;
							default: break;
						}
						echo '</div>';
					}
				}
			}
			if ($pdata->poll_results_msg_mod) echo $pdata->poll_results_msg_mod;

			if ($params->get( 'showresultslink', 0 )) {
				echo '<p align="center"><a href="'.JRoute::_('index.php?option=com_mpoll&task=results&poll=').'" class="button uk-button uk-button-default">Results</a></p>';
			}
		}
		?> </div>
</form>
