<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
if ($this->params->get('divwrapper',1)) {
	echo '<div id="system" class="'.$this->params->get('wrapperclass','uk-article').'">';
}
echo '<h2 class="title uk-article-title">'.$this->pdata->poll_name.'</h2>';

$user = JFactory::getUser();
$cfg = MPollHelper::getConfig();
$ri = 0;

/*** DISPLAY POLL ***/
if ($this->task=='ballot') {   ?>

	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#mpollform").validate({
				errorClass:"uk-form-danger",
				validClass:"uk-form-success",
				errorElement: "div",
				errorPlacement: function(error, element) {
			    	error.appendTo( element.parent("div"));
			    	error.addClass("uk-alert uk-alert-danger uk-form-controls-text");	
				}
		    });
		});

        function reCapChecked() {
            jQuery('#reCapChecked').val("checked");
        }

    </script>

	<?php 
	
	//Alert based on user status
	if ($this->pdata->poll_regreq == 1 && $user->id == 0) {
		echo '<div class="uk-alert uk-alert-warning">'.$this->pdata->poll_regreqmsg.'</div>';
	} else if ( !in_array($this->pdata->access,$user->getAuthorisedViewLevels())) {
		echo '<div class="uk-alert uk-alert-danger">'.$this->pdata->poll_accessreqmsg.'</div>';
	}
	
	//Message before Questions
	echo $this->pdata->poll_desc;

	if (!$this->ended) {

		//Begin Form
		echo '<form name="mpollform" id="mpollform" method="post" action="" enctype="multipart/form-data" class="uk-form';
		if ( $this->pdata->poll_pagetype == "form" ) {
			echo ' uk-form-horizontal';
		}
		echo '"><input type="hidden" name="stepnext" value="">';

		foreach ( $this->qdata as $f ) {

			//Debug
			//echo '<pre>'; print_r($f); echo '</pre>';

			$sname = 'q_' . $f->q_id;
			if ( $ri == 1 ) {
				$ri = 0;
			} else {
				$ri = 1;
			}

			//Start Row
			echo '<div class="row-' . $sname . ' mpoll-form-' . $this->pdata->poll_pagetype . '-row' . ( $ri % 2 ) . ' uk-form-row uk-margin-top">';

			//field title/label
			if ( $f->q_type != "message" && $f->q_type != "header" ) {
				echo '<div class="uk-form-label uk-text-bold">';
				if ( $f->q_req && $this->params->get( 'showreq', 1 ) ) {
					echo "*";
				}
				if ( $f->q_type != "cbox" && $f->q_type != "message" && $f->q_type != "header" ) {
					echo $f->q_text;
				}
				echo '</div>';
			}

			//Start Field
			if ( $f->q_type == "message" ) {
				echo '<div class="uk-alert">';
			} else if ( $f->q_type == "header" ) {
				echo '<div class="uk-margin-top uk-text-bold uk-text-large">';
			} else {
				echo '<div class="uk-form-controls';
				if ( $f->q_type != "cbox" || $f->q_pretext || $f->q_hint ) {
					echo ' uk-form-controls-text';
				}
				echo '">';
			}

			//Pretext
			if ( $f->q_pretext ) {
				echo '<div class="">' . $f->q_pretext . '</div>';
			}

			//Limit warnings
			if ( $f->q_type == "mcbox" || $f->q_type == "mlist" ) {
				if ( ! $f->q_min && ! $f->q_max ) {
					echo '<em>(Select all that apply)</em><br />';
				}
				if ( $f->q_min && ! $f->q_max ) {
					echo '<em>(Select at least ' . $f->q_min . ')</em><br />';
				}
				if ( ! $f->q_min && $f->q_max ) {
					echo '<em>(Select at most ' . $f->q_max . ')</em><br />';
				}
				if ( $f->q_min && $f->q_max ) {
					echo '<em>(Select at least ' . $f->q_min . ' and at most ' . $f->q_max . ')</em><br />';
				}
			}

			//Message
			if ( $f->q_type == "message" ) {
				echo $f->q_text;
			}
			if ( $f->q_type == "header" ) {
				echo $f->q_text;
			}


			//checkbox
			if ( $f->q_type == "cbox" ) {
				if ( ! empty( $f->value ) && $f->q_type == "cbox" ) {
					$checked = ( $f->value == '1' ) ? ' checked="checked"' : '';
				} else {
					$checked = '';
				}
				echo '<input type="checkbox" name="jform[' . $sname . ']" id="jform_' . $sname . '" class="uk-checkbox"';
				if ( $f->q_req && $f->q_type == "cbox" ) {
					echo ' data-rule-required="true" data-msg-required="This Field is required"';
				}
				echo $checked . '/>' . "\n";
				echo '<label for="jform_' . $sname . '">';
				echo ' ' . $f->q_text . '</label><br />' . "\n";
			}

			//multi checkbox
			if ( $f->q_type == "mcbox" ) {
				$first = true;
				foreach ( $f->options as $o ) {
					if ( $o->opt_selectable ) {
						if ( ! empty( $f->value ) ) {
							$checked = in_array( $o->value, $f->value ) ? ' checked="checked"' : '';
						} else {
							$checked = '';
						}
						echo '<input type="checkbox" name="jform[' . $sname . '][]" value="' . $o->value . '" class="uk-checkbox" id="jform_' . $sname . $o->value . '"';
						if ( $f->q_req && $first ) {
							echo ' data-rule-required="true"';
							if ( $f->q_min ) {
								echo ' data-rule-minlength="' . $f->q_min . '"';
							}
							if ( $f->q_max ) {
								echo ' data-rule-maxlength="' . $f->q_max . '"';
							}
							echo ' data-msg-required="This Field is required"';
							if ( $f->q_min ) {
								echo ' data-msg-minlength="Select at least ' . $f->q_min . '"';
							}
							if ( $f->q_max ) {
								echo ' data-msg-maxlength="Select at most ' . $f->q_max . '"';
							}
							$first = false;
						}
						if ( $o->opt_disabled ) {
							$checked .= ' disabled';
						}
						echo $checked . '/>' . "\n";
						echo '<label for="jform_' . $sname . $o->value . '">';
						echo ' ' . $o->text . '</label><br />' . "\n";
					} else {
						echo '<span class="uk-text-bold">' . $o->text . '</span><br />';
					}

				}
			}

			//radio
			if ( $f->q_type == "multi" ) {

				$first = true;
				foreach ( $f->options as $o ) {
					if ( $o->opt_selectable ) {
						if ( ! empty( $f->value ) ) {
							$checked = in_array( $o->value, $f->value ) ? ' checked="checked"' : '';
						} else {
							$checked = '';
						}
						echo '<input type="radio" name="jform[' . $sname . ']" value="' . $o->value . '" id="jform_' . $sname . $o->value . '" class="uk-radio"';
						if ( $f->q_req && $first ) {
							echo ' data-rule-required="true" data-msg-required="This Field is required"';
							$first = false;
						}
						if ( $o->opt_disabled ) {
							$checked .= ' disabled';
						}
						echo $checked . '/>' . "\n";
						echo '<label for="jform_' . $sname . $o->value . '">';
						echo ' ' . $o->text;
						if ( $o->opt_other ) {
							echo ' <input type="text" value="' . $f->other . '" name="jform[' . $sname . '_other]" id="jform_' . $sname . $o->value . '_other" class="">';
						}
						echo '</label>';
						echo '<br />' . "\n";
					} else {
						echo '<span class="uk-text-bold">' . $o->text . '</span><br />';
					}

				}
			}

			//dropdown
			if ( $f->q_type == "dropdown" ) {
				echo '<select id="jform_' . $sname . '" name="jform[' . $sname . ']" class="uk-width-1-1 uk-select"';
				if ( $f->q_req ) {
					echo ' data-rule-required="true" data-msg-required="This Field is required"';
				}
				echo '>';
				foreach ( $f->options as $o ) {
					if ( ! empty( $f->value ) ) {
						$selected = in_array( $o->value, $f->value ) ? ' selected="selected"' : '';
					} else {
						$selected = '';
					}
					if ( $o->opt_disabled ) {
						$selected .= ' disabled';
					}
					echo '<option value="' . $o->value . '"' . $selected . '>';
					echo ' ' . $o->text . '</option>';
				}
				echo '</select>';
			}

			//multilist
			if ( $f->q_type == "mlist" ) {
				echo '<select id="jform_' . $sname . '" name="jform[' . $sname . '][]" class="uk-width-1-1 uk-select" size="4" multiple="multiple"';
				if ( $f->q_req ) {
					echo ' data-rule-required="true"';
					if ( $f->q_min ) {
						echo ' data-rule-minlength="' . $f->q_min . '"';
					}
					if ( $f->q_max ) {
						echo ' data-rule-maxlength="' . $f->q_max . '"';
					}
					echo ' data-msg-required="This Field is required"';
					if ( $f->q_min ) {
						echo ' data-msg-minlength="Select at least ' . $f->q_min . '"';
					}
					if ( $f->q_max ) {
						echo ' data-msg-maxlength="Select at most ' . $f->q_max . '"';
					}
					$first = false;
				}
				echo '>';
				foreach ( $f->options as $o ) {
					if ( ! empty( $f->value ) ) {
						$selected = in_array( $o->value, $f->value ) ? ' selected="selected"' : '';
					} else {
						$selected = '';
					}
					if ( $o->opt_disabled ) {
						$selected .= ' disabled';
					}
					echo '<option value="' . $o->value . '"' . $selected . '>';
					echo ' ' . $o->text . '</option>';
				}
				echo '</select>';
			}


			//text field, phone #, email, username
			if ( $f->q_type == "textbox" || $f->q_type == "email" ) {
				echo '<input name="jform[' . $sname . ']" id="jform_' . $sname . '" value="' . $f->value . '" class="mf_field uk-width-1-1 uk-input" type="text"';
				if ( $f->q_req ) {
					echo ' data-rule-required="true"';
					if ( $f->q_min ) {
						echo ' data-rule-minlength="' . $f->q_min . '"';
					}
					if ( $f->q_max ) {
						echo ' data-rule-maxlength="' . $f->q_max . '"';
					}
					if ( $f->q_type == "email" ) {
						echo ' data-rule-email="true"';
					}
					if ( $f->q_match ) {
						echo ' data-rule-equalTo="#jform_' . $f->q_match . '"';
					}
					echo ' data-msg-required="This Field is required"';
					if ( $f->q_min ) {
						echo ' data-msg-minlength="Min length ' . $f->q_min . ' characters"';
					}
					if ( $f->q_max ) {
						echo ' data-msg-maxlength="Max length ' . $f->q_max . ' characters"';
					}
					if ( $f->q_type == "email" ) {
						echo ' data-msg-email="Email address must be valid"';
					}
					if ( $f->q_match ) {
						echo ' data-msg-equalTo="Fields must match"';
					}
				}
				echo '>';
			}

			//text area
			if ( $f->q_type == "textar" ) {
				echo '<textarea name="jform[' . $sname . ']" id="jform_' . $sname . '" cols="70" rows="4" class="mf_field uk-width-1-1 uk-textarea"';
				if ( $f->q_req ) {
					echo ' data-rule-required="true" data-msg-required="This Field is required"';
				}
				echo '>' . $f->value . '</textarea>';
			}

			//captcha
			if ( $f->q_type == "captcha" ) {
				echo '<img id="captcha_img" src="' . JURI::base( true ) . '/components/com_mpoll/lib/securimage/securimage_show.php" alt="CAPTCHA Image" />';
				echo '<input name="jform[' . $sname . ']" id="jform_' . $sname . '" value="" class="mf_field uk-width-1-1 uk-input" type="text"';
				if ( $f->q_req ) {
					echo ' data-rule-required="true"';
					echo ' data-msg-required="This Field is required"';
				}
				echo '>';
				echo '<div class="uk-text-small">';
				echo '<a href="#" onclick="document.getElementById(\'captcha_img\').src = \'' . JURI::base( true ) . '/components/com_mpoll/lib/securimage/securimage_show.php?\' + Math.random(); return false">Reload Image</a>';
				echo '</div>';
			}

			//File Attachment
			if ( $f->q_type == 'attach' ) {
				echo '<input name="q_' . $f->q_id . '[]" id="jform_' . $sname . '" type="file" size="40" multiple="multiple" class="mf_file"';
				if ( $f->q_req ) {
					echo ' data-rule-required="true"';
					echo ' data-msg-required="This Field is required"';
				}
				echo ' />';
			}

			if ( $f->q_hint && $f->q_type != "captcha" ) {
				echo '<div class="uk-text-small">' . $f->q_hint . '</div>';
			}

			//End Field
			echo '</div>';

			//End Row
			echo '</div>';
		}

		//reCAPTCHA
		if ( $this->pdata->poll_recaptcha ) {
			echo '<div class="uk-form-row uk-margin-top mpoll-form-' . $this->pdata->poll_pagetype . '-row' . ( $ri % 2 ) . '">';
			echo '<div class="uk-form-label">';
			echo '</div>';
			echo '<div class="uk-form-controls">';
			echo '<input type="hidden" id="reCapChecked" name="reCapChecked" value="" data-rule-required="true" data-msg-required="reCaptcha Required">';
			echo '<div class="g-recaptcha" data-callback="reCapChecked" data-theme="' . $cfg->rc_theme . '" data-sitekey="' . $cfg->rc_api_key . '"></div>';
			echo '</div></div>';
		}

		//Submit
		echo '<div class="uk-form-row uk-margin-top">';
		echo '<div class="uk-form-controls">';
		if ( $this->pdata->poll_regreq == 1 && $user->id == 0 ) {
			echo '<div class="uk-alert uk-alert-warning">' . $this->pdata->poll_regreqmsg . '</div>';
		} else {
			if ( in_array( $this->pdata->access, $user->getAuthorisedViewLevels() ) ) {
				echo JHtml::_( 'form.token' );
				echo '<input name="castvote" id="castvote" value="Submit" type="submit" class="button uk-button uk-button-default">';
			} else {
				echo '<div class="uk-alert uk-alert-danger">' . $this->pdata->poll_accessreqmsg . '</div>';
			}
		}
		echo '</div></div>';

		echo '<input type="hidden" name="option" value="com_mpoll">';
		echo '<input type="hidden" name="task" value="castvote">';
		echo '<input type="hidden" name="return" value="' . base64_encode( $this->return ) . '">';
		echo '</form>';
	}
	
} 

/*** DISPLAY POLL RESULTS ***/
if ($this->task=='results') { 
	
	//Print button
	if ($this->pdata->poll_printresults) {
		$url = 'index.php?option=com_mpoll&task=results&tmpl=component&print=1&poll='.$this->pdata->poll_id.'&cmplid='.$this->cmplid;
		if ($this->print) echo '<p><a href="javascript:print()" class="button uk-button uk-button-default">Print</a></p>';
		else echo '<p><a href="'.JRoute::_($url).'" class="button uk-button uk-button-default" target="_blank">Print</a></p>';
	}
	
	//Process results message
	foreach ($this->qdata as $q) {
		if ($q->answer) {
			$answer="";
			if ($q->q_type != 'mcbox' && $q->q_type != "mlist") {
				if ($q->q_type == "multi" || $q->q_type == "dropdown") {
					foreach ($q->options as $o) {
						if ($o->value == $q->answer) $answer=$o->text;
					}					
				} else { $answer = $q->answer; }
			} else {
				$answers = explode(" ",$q->qnswer);
				$result = array();
				foreach ($q->options as $o) {
					if (in_array($o->value,$answers)) $result[] = $o->text;
				}
				$answer = implode(", ",$answers);
			}
			$this->pdata->poll_results_msg_before = str_replace("{i".$q->q_id."}",$answer,$this->pdata->poll_results_msg_before);
		}
	}
	
	//Display before results message
	if ($this->pdata->poll_results_msg_before) {
		$this->pdata->poll_results_msg_before = str_replace("{resid}",$this->cmplid,$this->pdata->poll_results_msg_before);
		echo $this->pdata->poll_results_msg_before;
	}
	
	//Display results for multi/single choice questions
	if ($this->pdata->poll_showresults) {
		foreach ($this->qdata as $q) {
			if ($q->q_type == "mcbox" || $q->q_type == "multi" || $q->q_type == "dropdown" || $q->q_type == "mlist") {
				echo '<div class="mpollcom-question">';
				$anscor=false;
				echo '<div class="mpollcom-question-text">'.$q->q_text.'</div>';
				switch ($q->q_type) {
					case 'multi':
					case 'mcbox':
					case 'mlist':
					case 'dropdown':
						$numr=0;
						foreach ($q->options as $opts) {
							if ($opts->opt_selectable) {
								if ($q->anscount != 0) $per = ($opts->anscount)/($q->anscount); else $per=1;
								echo '<div class="mpollcom-opt">';
								echo '<div class="mpollcom-opt-text">';
								if ($opts->opt_correct) echo '<div class="mpollcom-opt-correct">'.$opts->text.'</div>';
								else echo $opts->text;
								echo '</div>';
								echo '<div class="mpollcom-opt-count">';
								if ($this->params->get('resultsas','percent') == "percent") echo (int)($per*100)."%";
								else echo ($opts->anscount);
								echo '</div>';
								echo '<div class="mpollcom-opt-bar-box"><div class="mpollcom-opt-bar-bar" style="background-color: '.$opts->opt_color.'; width:'.($per*100).'%"></div></div>';
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
	
	//Display After results message
	if ($this->pdata->poll_results_msg_after) echo $this->pdata->poll_results_msg_after;
	
	//Display stats
	if ($this->params->get('showstats',1)) {
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
	
}
if ($this->params->get('divwrapper',1)) { echo '</div>'; }
?>



