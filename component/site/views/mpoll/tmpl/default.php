<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
echo '<div id="system" class="uk-article">';
$db =& JFactory::getDBO();
$user = JFactory::getUser();
if ($this->showlist != 'never') {
	$jumplist  = '<form name="jumppoll" action="" method="post">';
	$jumplist .= '<p align="center">Select Poll: ';
	$jumplist .= JHTML::_('select.genericlist',$this->polllist,'poll','onchange="changePoll();"','poll_id','poll_name',$this->pdata->poll_id);
	$jumplist .= '<input type="hidden" name="jumptask" value="'.$this->task.'">';
	$jumplist .= '<input type="hidden" name="option" value="com_mpoll">';
	$jumplist .= '<input type="hidden" name="task" value="gotopoll">';
	$jumplist .= '</p>';
	$jumplist .= '</form>';
}

if ($this->task=='ballot') {  /*** DISPLAY POLL ***/
	if ($this->showlist == 'both') echo $jumplist;
	?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#mpollform").validate({
			errorClass:"uk-form-danger",
			validClass:"uk-form-success",
			errorElement: "div",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div"));
		    	error.addClass("uk-alert uk-alert-danger uk-form-controls-text")
		    }

	    });

	});


</script>
	<?php 
	echo '<h2 class="title uk-article-title">'.$this->pdata->poll_name.'</h2>';
	if ($this->pdata->poll_regreq == 1 && $user->id == 0) {
		echo '<div class="uk-alert uk-alert-warning">'.$this->pdata->poll_regreqmsg.'</div>';
	} else if ( !in_array($this->pdata->access,$user->getAuthorisedViewLevels())) {
		echo '<div class="uk-alert uk-alert-danger">'.$this->pdata->poll_accessreqmsg.'</div>';
	}
	echo $this->pdata->poll_desc;
	
	echo '<form name="mpollform" id="mpollform" method="post" action="" enctype="multipart/form-data" class="uk-form';
	if ($this->pdata->poll_pagetype == "form") echo ' uk-form-horizontal';
	echo '"><input type="hidden" name="stepnext" value="">';
	
	foreach($this->qdata as $f) {
		
		//Debug
		//echo '<pre>'; print_r($f); echo '</pre>';
		
		$sname = 'q_'.$f->q_id;
		if ($ri==1) $ri=0;
		else $ri=1;
		echo '<div class="row-'.$sname.' mpoll-form-'.$this->pdata->poll_pagetype.'-row'.($ri % 2);
		if ($f->q_type != "message" && $f->q_type != "header") echo ' uk-form-row';
		echo '">';
		
		//field title/label
		if ($f->q_type != "message" && $f->q_type != "header") {
			echo '<div class="uk-form-label uk-text-bold">';
			if ($f->q_req) echo "*";
					if ($f->q_type != "cbox" && $f->q_type != "message" && $f->q_type != "header" && $f->q_type != "mailchimp") echo $f->q_text;
			echo '</div>';
		}
		
		//Field
		if ($f->q_type == "message") echo '<div class="uk-alert">';
		else if ($f->q_type == "header") echo '<div class="uk-margin-top uk-text-bold uk-text-large">';
		else {
			echo '<div class="uk-form-controls';
			if ($f->q_type != "cbox" || $f->q_type != "mailchimp" || $f->q_pretext || $f->q_hint) echo ' uk-form-controls-text';
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
	
	
		//checkbox & mailchimp list
		if ($f->q_type=="cbox" || $f->q_type=="mailchimp") {
			if (!empty($f->value) && $f->q_type=="cbox") $checked = ($f->value == '1') ? ' checked="checked"' : '';
			else if ($f->params->mc_checked == "1") $checked = ' checked="checked"';
			else $checked = '';
			echo '<input type="checkbox" name="jform['.$sname.']" id="jform_'.$sname.'" class=""';
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
					echo '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->value.'" class="" id="jform_'.$sname.$o->value.'"';
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
					echo ' '.$o->text.'</label><br />'."\n";
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
					echo '<input type="radio" name="jform['.$sname.']" value="'.$o->value.'" id="jform_'.$sname.$o->value.'" class=""';
					if ($f->q_req && $first) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; $first=false;}
					if ($o->opt_disabled) $checked .= ' disabled';
					echo $checked.'/>'."\n";
					echo '<label for="jform_'.$sname.$o->value.'">';
					echo ' '.$o->text;
					if ($o->opt_other) {
						echo ' <input type="text" value="'.$f->other.'" name="jform['.$sname.'_other]" id="jform_'.$sname.$o->value.'_other" class="">';
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
			echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="uk-width-1-1"';
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
			echo '<select id="jform_'.$sname.'" name="jform['.$sname.'][]" class="uk-width-1-1" size="4" multiple="multiple"';
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
			echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$f->value.'" class="mf_field uk-width-1-1" type="text"';
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
			echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="mf_field uk-width-1-1"';
			if ($f->q_req) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
			echo '>'.$f->value.'</textarea>';
		}
	
		//captcha
		if ($f->q_type=="captcha") {
			echo '<img id="captcha_img" src="'.JURI::base(true).'/components/com_mpoll/lib/securimage/securimage_show.php" alt="CAPTCHA Image" />';
			echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="" class="mf_field" type="text"';
			if ($f->q_req) {
				echo ' data-rule-required="true"';
				echo ' data-msg-required="This Field is required"';
			}
			echo '>';
			echo '<div class="uk-text-small">';
			echo '<a href="#" onclick="document.getElementById(\'captcha_img\').src = \''.JURI::base(true).'/components/com_mpoll/lib/securimage/securimage_show.php?\' + Math.random(); return false">Reload Image</a>';
			echo '</div>';
		}
		
		//File Attachment
		if ($f->q_type == 'attach') {
			echo '<input name="q_'.$f->q_id.'" id="jform_'.$sname.'" type="file" size="40" class="mf_file"';
			if ($f->q_req) {
				echo ' data-rule-required="true"';
				echo ' data-msg-required="This Field is required"';
			}
			echo ' />';
		}
	
		if ($f->q_hint && $f->q_type!="captcha") echo '<div class="uk-text-small">'.$f->q_hint.'</div>';
	
		echo '</div>';
		
		echo '</div>';
	}

	//Submit
	echo '<div class="uk-form-row">';
	echo '<div class="uk-form-controls">';
	if ($this->pdata->poll_regreq == 1 && $user->id == 0) {
		echo '<div class="uk-alert uk-alert-warning">'.$this->pdata->poll_regreqmsg.'</div>';
	} else {
		if ( in_array($this->pdata->access,$user->getAuthorisedViewLevels())) {
			echo JHtml::_('form.token');
			echo '<input name="castvote" id="castvote" value="Submit" type="submit" class="button uk-button">';
		} else {
			echo '<div class="uk-alert uk-alert-danger">'.$this->pdata->poll_accessreqmsg.'</div>';
		}
	}
	echo '</div></div>';
	
	/*
	 * foreach($this->qdata as $f) {
		
		//Debug
		//echo '<pre>'; print_r($f); echo '</pre>';
		
		$sname = 'q_'.$f->q_id;
		if ($ri==1) $ri=0;
		else $ri=1;
		echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-row row-'.$sname.' mpoll-form-'.$this->pdata->poll_pagetype.'-row'.($ri % 2).'">';
		echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-label">';
		if ($f->q_req) echo "*";
		
		//field title/label
		if ($f->q_type != "cbox" && $f->q_type != "message" && $f->q_type != "header" && $f->q_type != "mailchimp") echo $f->q_text;
		echo '</div>';
		if ($f->q_type == "message") echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-msg">';
		else if ($f->q_type == "header") echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-hdr">';
		else echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-value">';
		if ($f->q_type == "mcbox" || $f->q_type == "mlist") {
			if (!$f->q_min && !$f->q_max) echo '<em>(Select all that apply)</em><br />';
			if ($f->q_min && !$f->q_max) echo '<em>(Select at least '.$f->q_min.')</em><br />';
			if (!$f->q_min && $f->q_max) echo '<em>(Select at most '.$f->q_max.')</em><br />';
			if ($f->q_min && $f->q_max) echo '<em>(Select at least '.$f->q_min.' and at most '.$f->q_max.')</em><br />';
		}
	
		//Message
		if ($f->q_type == "message") echo '<strong>'.$f->q_text.'</strong>';
		if ($f->q_type == "header") echo '<strong>'.$f->q_text.'</strong>';
	
		//Pretext
		if ($f->q_pretext) echo '<span class="mf_pretext">'.$f->q_pretext.'</span>';
	
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
			echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$f->value.'" class="mf_field uk-width-1-1" type="text"';
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
			echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="mf_field uk-width-1-1"';
			if ($f->q_req) { echo ' data-rule-required="true" data-msg-required="This Field is required"'; }
			echo '>'.$f->value.'</textarea>';
			echo '</div></div>';
		}
	
		//captcha
		if ($f->q_type=="captcha") {
			echo '<div class="mform-field">';
			echo '<div class="mform-field-captcha">';
			echo '<img id="captcha_img" src="'.JURI::base(true).'/components/com_mpoll/lib/securimage/securimage_show.php" alt="CAPTCHA Image" />';
			echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="" class="mf_field" type="text"';
			if ($f->q_req) {
				echo ' data-rule-required="true"';
				echo ' data-msg-required="This Field is required"';
			}
			echo '>';
			echo '<div class="mf_note">';
			echo '<a href="#" onclick="document.getElementById(\'captcha_img\').src = \''.JURI::base(true).'/components/com_mpoll/lib/securimage/securimage_show.php?\' + Math.random(); return false">Reload Image</a>';
			echo '</div>';
			echo '</div></div>';
		}
		
		//File Attachment
		if ($f->q_type == 'attach') {
			echo '<div class="mform-field">';
			echo '<div class="mform-field-attach">';
			echo '<input name="q_'.$f->q_id.'" id="jform_'.$sname.'" type="file" size="40" class="mf_file"';
			if ($f->q_req) {
				echo ' data-rule-required="true"';
				echo ' data-msg-required="This Field is required"';
			}
			echo ' />';
			echo '</div></div>'; 
		}
	
		if ($f->q_hint && $f->q_type!="captcha") echo '<div class="mf_note">'.$f->q_hint.'</div>';
	
		echo '</div>';
		echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-error">';
		echo '</div>';
		echo '</div>';
	}
	
	
	//Submit
	echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-row">';
	echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-label">';
	echo '</div>';
	echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-submit">';
	if ($this->pdata->poll_regreq == 1 && $user->id == 0) {
		echo $this->pdata->poll_regreqmsg;
	} else { 
		if ( in_array($this->pdata->access,$user->getAuthorisedViewLevels())) {
			echo JHtml::_('form.token');
			echo '<input name="castvote" id="castvote" value="Submit" type="submit" class="button uk-button">';
		} else {
			echo $this->pdata->poll_accessreqmsg;
		}
	}
	echo '</div></div>';
	 */
		
	echo '<input type="hidden" name="option" value="com_mpoll">';
	echo '<input type="hidden" name="casting" value="true">';
	echo '<input type="hidden" name="return" value="'.base64_encode($this->return).'">';
	echo '</form>';
	
} else if ($this->task=='results') { /*** DISPLAY POLL RESULTS ***/
	if (($this->showlist == 'both' || $this->showlist == 'after')) echo $jumplist;
	echo '<h2 class="title">'.$this->pdata->poll_name.'</h2>';
	if ($this->pdata->poll_printresults) {
		$url = 'index.php?option=com_mpoll&task=results&tmpl=component&print=1&poll='.$this->pdata->poll_id.'&cmplid='.$this->cmplid;
		if ($this->print) echo '<p><a href="javascript:print()" class="button uk-button">Print</a></p>';
		else echo '<p><a href="'.JRoute::_($url).'" class="button uk-button" target="_blank">Print</a></p>';
	}
	foreach ($this->qdata as $q) {
		if ($q->answer) {
			if ($q->q_type != 'mcbox' && $q->q_type != "mlist") {
				if ($q->q_type == "multi" || $q->q_type == "dropdown") {
					$qo = 'SELECT opt_txt FROM #__mpoll_questions_opts WHERE published > 0 && opt_id = '.$q->answer;
					$db->setQuery($qo); $opt = $db->loadObject();
					$result = $opt->opt_txt;
					$this->pdata->poll_results_msg_before = str_replace("{i".$q->q_id."}",$result,$this->pdata->poll_results_msg_before);
				} else {
					$this->pdata->poll_results_msg_before = str_replace("{i".$q->q_id."}",$q->answer,$this->pdata->poll_results_msg_before);
				}
			} else {
				if ($q->answer) {
					$qo = 'SELECT opt_txt FROM #__mpoll_questions_opts WHERE published > 0 && opt_id IN ('.str_replace(' ',',',$q->answer).')';
					$db->setQuery($qo); $opts = $db->loadResultArray();
					if ($opts) {
						foreach ($opts as $o) {
							$result = $o->opt_txt;
							$cfans .= $result.', ';
						}
					}
				}
				$this->pdata->poll_results_msg_before = str_replace("{i".$q->q_id."}",$cfans,$this->pdata->poll_results_msg_before);
			}
		}
	}
	if ($this->pdata->poll_results_msg_before) {

		$this->pdata->poll_results_msg_before = str_replace("{resid}",$this->cmplid,$this->pdata->poll_results_msg_before);
		echo $this->pdata->poll_results_msg_before;
	}
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
						//$qnum = 'SELECT count(res_qid) FROM #__mpoll_results WHERE res_qid = '.$q->q_id.' GROUP BY res_qid';
						//$db->setQuery( $qnum );
						//$qnums = $db->loadAssoc();
						$numr=0; //$qnums['count(res_qid)'];
						foreach ($q->options as &$o) {
							$qa = 'SELECT count(*) FROM #__mpoll_results WHERE res_qid = '.$q->q_id.' && res_ans LIKE "%'.$o->value.'%" GROUP BY res_qid';
							$db->setQuery($qa);
							$o->anscount = $db->loadResult();
							if ($o->anscount == "") $o->anscount = 0;
							$numr = $numr + (int)$o->anscount;
						}
						foreach ($q->options as $opts) {
							if ($opts->opt_selectable) {
								if ($numr != 0) $per = ($opts->anscount)/($numr); else $per=1;
								echo '<div class="mpollcom-opt">';
								
								echo '<div class="mpollcom-opt-text">';
								if ($opts->opt_correct) echo '<div class="mpollcom-opt-correct">'.$opts->text.'</div>';
								else echo $opts->text;
								echo '</div>';
								echo '<div class="mpollcom-opt-count">';
								if ($this->resultsas == "percent") echo (int)($per*100)."%";
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
	if ($this->pdata->poll_results_msg_after) echo $this->pdata->poll_results_msg_after;
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
</div>

