<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

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
		jQuery.metadata.setType("attr", "validate");
		jQuery("#mpollform").validate({
			errorClass:"mf_error",
			validClass:"mf_valid",
			errorPlacement: function(error, element) {
		    	error.appendTo( element.parent("div").parent("div").next("div") );
		    }
	    });

	});


</script>
	<?php 
	echo '<h2 class="componentheading">'.$this->pdata->poll_name.'</h2>';
	if ($this->pdata->poll_regreq == 1 && $user->id == 0) {
		echo '<p align="center">'.$this->pdata->poll_regreqmsg.'</p>';
	} 
	echo $this->pdata->poll_desc;
	echo '<div id="mpoll-form-'.$this->pdata->poll_pagetype.'">';
	echo '<form name="mpollform" id="mpollform" method="post" action="" onSubmit="return checkRq();" enctype="multipart/form-data"><input type="hidden" name="stepnext" value="">';
	
	foreach($this->qdata as $f) {
		echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-row">';
		echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-label">';
		if ($f->q_req) echo "*";
		$sname = 'q_'.$f->q_id;
		//field title
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
		if ($f->q_type=="cbox") {
			echo '<div class="mform-radio">';
			if (!empty($f->value)) $checked = ($f->value == '1') ? ' checked="checked"' : '';
			else $checked = '';
			echo '<input type="checkbox" name="jform['.$sname.']" id="jform_'.$sname.'" class="uf_radio"';
			if ($f->q_req && $f->q_type=="cbox") { echo ' validate="{required:true, messages:{required:\'This Field is required\'}}"'; }
			echo $checked.'/>'."\n";
			echo '<label for="jform_'.$sname.'">';
			echo ' '.$f->q_text.'</label><br />'."\n";
			echo '</div>';
		}
	
		//multi checkbox
		if ($f->q_type=="mcbox") {
			echo '<div class="mform-radio">';
			$first = true;
			foreach ($f->options as $o) {
				if (!empty($f->value)) $checked = in_array($o->value,$f->value) ? ' checked="checked"' : '';
				else $checked = '';
				echo '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->value.'" class="uf_radio" id="jform_'.$sname.$o->value.'"';
				if ($f->q_req && $first) {
					echo ' validate="{required:true';
					if ($f->q_min) echo ', minlength:'.$f->q_min;
					if ($f->q_max) echo ', maxlength:'.$f->q_max;
					echo ', messages:{required:\'This Field is required\'';
					if ($f->q_min) echo ', minlength:\'Select at least '.$f->q_min.'\'';
					if ($f->q_max) echo ', maxlength:\'Select at most '.$f->q_max.'\'';
					echo '}}"';
					$first=false;
				}
				if ($o->opt_disabled) $checked .= ' disabled';
				echo $checked.'/>'."\n";
				echo '<label for="jform_'.$sname.$o->value.'">';
				echo ' '.$o->text.'</label><br />'."\n";
					
			}
			echo '</div>';
		}
	
		//radio, limitable select
		if ($f->q_type=="multi") {
			echo '<div class="mform-radio">';
			$first=true;
			foreach ($f->options as $o) {
				if (!empty($f->value)) $checked = in_array($o->value,$f->value) ? ' checked="checked"' : '';
				else $checked = '';
				echo '<input type="radio" name="jform['.$sname.']" value="'.$o->value.'" id="jform_'.$sname.$o->value.'" class="uf_radio"';
				if ($f->q_req && $first) { echo ' validate="{required:true, messages:{required:\'This Field is required\'}}"'; $first=false;}
				if ($o->opt_disabled) $checked .= ' disabled';
				echo $checked.'/>'."\n";
				echo '<label for="jform_'.$sname.$o->value.'">';
				echo ' '.$o->text;
				echo '</label><br />'."\n";
					
			}
			echo '</div>';
		}
	
		//dropdown
		if ($f->q_type=="dropdown") {
			echo '<div class="mform-field">';
			echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="mf_field mf_select" size="1"';
			if ($f->q_req) { echo ' validate="{required:true, messages:{required:\'This Field is required\'}}"'; }
			echo '>';
			foreach ($f->options as $o) {
				if (!empty($f->value)) $selected = in_array($o->value,$f->value) ? ' selected="selected"' : '';
				else $selected = '';
				if ($o->opt_disabled) $selected .= ' disabled';
				echo '<option value="'.$o->value.'"'.$selected.'>';
				echo ' '.$o->text.'</option>';
			}
			echo '</select>';
			echo '</div>';
		}
	
		//multilist
		if ($f->q_type=="mlist") {
			echo '<div class="mform-field">';
			echo '<select id="jform_'.$sname.'" name="jform['.$sname.'][]" class="mf_field mf_mselect" size="4" multiple="multiple"';
			if ($f->q_req) {
				echo ' validate="{required:true';
				if ($f->q_min) echo ', minlength:'.$f->q_min;
				if ($f->q_max) echo ', maxlength:'.$f->q_max;
				echo ', messages:{required:\'This Field is required\'';
				if ($f->q_min) echo ', minlength:\'Select at least '.$f->q_min.'\'';
				if ($f->q_max) echo ', maxlength:\'Select at most '.$f->q_max.'\'';
				echo '}}"';
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
			echo '</div>';
		}
	
	
		//text field, phone #, email, username
		if ($f->q_type=="textbox" || $f->q_type=="email" || $f->q_type=="username" || $f->q_type=="phone" || $f->q_type=="code") {
			echo '<div class="mform-field">';
			echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$f->value.'" class="mf_field" type="text"';
			if ($f->q_req) {
				echo ' validate="{required:true';
				if ($f->q_min) echo ', minlength:'.$f->q_min;
				if ($f->q_max) echo ', maxlength:'.$f->q_max;
				if ($f->q_type=="email") echo ', email:true';
				if ($f->q_match) echo ', equalTo: \'#jform_'.$f->q_match.'\'';
				if ($f->q_type == "code") echo ',remote: { url: \''.JURI::base( true ).'/components/com_mcor/helpers/chkcode.php?regtype='.$this->typeinfo[0]->ct_id.'\', type: \'post\'}';
				echo ', messages:{required:\'This Field is required\'';
				if ($f->q_min) echo ', minlength:\'Min length '.$f->q_min.' characters\'';
				if ($f->q_max) echo ', maxlength:\'Max length '.$f->q_max.' characters\'';
				if ($f->q_type=="email") echo ', email:\'Email address must be valid\'';
				if ($f->q_match) echo ', equalTo: \'Fields must match\'';
				if ($f->q_type=="code") echo ', remote:\'Invalid code\'';
				echo '}}"';
			} else if ($f->q_type == "code") {
				echo ' validate="{';
				echo 'remote: { url: \''.JURI::base( true ).'/components/com_mcor/helpers/chkcode.php?regtype='.$this->typeinfo[0]->ct_id.'\', type: \'post\'}';
				echo ', messages:{';
				echo 'remote:\'Invalid code\'';
				echo '}}"';
			}
			echo '>';
			echo '</div>';
		}
	
		//password
		if ($f->q_type=="password") {
			echo '<div class="mform-field">';
			echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" class="mf_field" size="20" type="password" ';
			echo 'validate="{required:true, minlength:8';
			if ($f->q_match) echo ', equalTo: \'#jform_'.$f->q_match.'\'';
			echo ', messages:{required:\'This Field is required\', minlength:\'Minimum length 8 characters\'';
			if ($f->q_match) echo ', equalTo: \'Fields must match\'';
			echo '}}">';
			echo '</div>';
		}
	
		//text area
		if ($f->q_type=="textar") {
			echo '<div class="mform-field">';
			echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="mf_field"';
			if ($f->q_req) { echo ' validate="{required:true, messages:{required:\'This Field is required\'}}"'; }
			echo '>'.$f->value.'</textarea>';
			echo '</div>';
		}
	
		//Yes no
		if ($f->q_type=="yesno") {
			echo '<div class="mform-field">';
			echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="mf_field" size="1">';
			$selected = ' selected="selected"';
			echo '<option value="1"';
			echo ($f->value == "1") ? $selected : '';
			echo '>Yes</option>';
			echo '<option value="0"';
			echo ($f->value == "0") ? $selected : '';
			echo '>No</option>';
	
			echo '</select>';
			echo '</div>';
	
		}
	
	
		//Birthday
		if ($f->q_type=="birthday") {
			echo '<div class="mform-field">';
			$selected = ' selected="selected"';
			echo '<select id="jform_'.$sname.'_month" name="jform['.$sname.'_month]" class="mf_bday_month">';
			echo '<option value="01"'; echo (substr($f->value,0,2) == "01") ? $selected : ''; echo '>01 - January</option>';
			echo '<option value="02"'; echo (substr($f->value,0,2) == "02") ? $selected : ''; echo '>02 - February</option>';
			echo '<option value="03"'; echo (substr($f->value,0,2) == "03") ? $selected : ''; echo '>03 - March</option>';
			echo '<option value="04"'; echo (substr($f->value,0,2) == "04") ? $selected : ''; echo '>04 - April</option>';
			echo '<option value="05"'; echo (substr($f->value,0,2) == "05") ? $selected : ''; echo '>05 - May</option>';
			echo '<option value="06"'; echo (substr($f->value,0,2) == "06") ? $selected : ''; echo '>06 - June</option>';
			echo '<option value="07"'; echo (substr($f->value,0,2) == "07") ? $selected : ''; echo '>07 - July</option>';
			echo '<option value="08"'; echo (substr($f->value,0,2) == "08") ? $selected : ''; echo '>08 - August</option>';
			echo '<option value="09"'; echo (substr($f->value,0,2) == "09") ? $selected : ''; echo '>09 - September</option>';
			echo '<option value="10"'; echo (substr($f->value,0,2) == "10") ? $selected : ''; echo '>10 - October</option>';
			echo '<option value="11"'; echo (substr($f->value,0,2) == "11") ? $selected : ''; echo '>11 - November</option>';
			echo '<option value="12"'; echo (substr($f->value,0,2) == "12") ? $selected : ''; echo '>12 - December</option>';
			echo '</select>';
			echo '<select id="jform_'.$sname.'_day" name="jform['.$sname.'_day]" class="mf_bday_day">';
			for ($i=1;$i<=31;$i++) {
				if ($i<10) $val = "0".$i;
				else $val=$i;
				echo '<option value="'.$val.'"';
				echo (substr($f->value,2,2) == $val) ? $selected : '';
				echo '>'.$val.'</option>';
			}
			echo '</select>';
			echo '</div>';
		}
	
	
		//captcha
		if ($f->q_type=="captcha") {
			echo '<div class="mform-field">';
			echo '<img id="captcha_img" src="'.JURI::base(true).'/components/com_mpoll/lib/securimage/securimage_show.php" alt="CAPTCHA Image" />';
			echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="" class="mf_field" type="text"';
			if ($f->q_req) {
				echo ' validate="{required:true';
				echo ', messages:{required:\'This Field is required\'';
				echo '}}"';
			}
			echo '>';
			echo '<span class="uf_note">';
			echo '<a href="#" onclick="document.getElementById(\'captcha_img\').src = \''.JURI::base(true).'/components/com_mue/lib/securimage/securimage_show.php?\' + Math.random(); return false">Reload Image</a>';
			echo '</span>';
			echo '</div>';
		}
		
		//File Attachment
		if ($f->q_type == 'attach') {
			echo '<div class="mform-field">';
			echo '<input name="q_'.$f->q_id.'" id="jform_'.$sname.'" type="file" size="40" class="mf_file" />';
			echo '</div>'; 
		}
	
		if ($f->q_hint && $f->q_type!="captcha") echo '<span class="mf_note">'.$f->q_hint.'</span>';
	
		echo '</div>';
		echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-error">';
		echo '</div>';
		echo '</div>';
	}
	
	echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-row">';
	echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-label">';
	echo '</div>';
	echo '<div class="mpoll-form-'.$this->pdata->poll_pagetype.'-submit">';
	if ($this->pdata->poll_regreq == 1 && $user->id == 0) {
		echo $this->pdata->poll_regreqmsg;
	} else { 
		echo '<input name="castvote" id="castvote" value="Submit" type="submit" class="button">';
	}
	echo '</div></div>';
	echo '<input type="hidden" name="option" value="com_mpoll">';
	echo '<input type="hidden" name="casting" value="true">';
	echo '<input type="hidden" name="jform[CTypeID]" value="'.$this->typeinfo[0]->ct_id.'">';
	echo '<input type="hidden" name="return" value="'.base64_encode($this->return).'">';
	echo JHtml::_('form.token');
	echo '</form>';
	echo '<div style="clear:both;"></div>';
	echo '</div>';
	
} else if ($this->task=='results') { /*** DISPLAY POLL RESULTS ***/
	if (($this->showlist == 'both' || $this->showlist == 'after')) echo $jumplist;
	echo '<h2 class="componentheading">'.$this->pdata->poll_name.'</h2>';
	foreach ($this->qdata as $q) {
		if ($q->answer) {
			if ($q->q_type != 'mcbox') {
				if ($q->q_type == "multi") {
					$qo = 'SELECT opt_txt FROM #__mpoll_questions_opts WHERE published > 0 && opt_id = '.$q->answer;
					$db->setQuery($qo); $opt = $db->loadObject();
					$result = $opt->opt_txt;
					$this->pdata->poll_results_msg_before = str_replace("{i".$q->q_id."}",$result,$this->pdata->poll_results_msg_before);
				} else {
					$this->pdata->poll_results_msg_before = str_replace("{i".$q->q_id."}",$q->answer,$this->pdata->poll_results_msg_before);
				}
			} else {
				$ansarr = $q->answer;
				$ans = implode(' ',$ansarr);
				$qo = 'SELECT opt_txt FROM #__mpoll_questions_opts WHERE published > 0 && opt_id IN ('.implode(',',$ans).')';
				$db->setQuery($qo); $opts = $db->loadResultArray();
				foreach ($opt as $o) {
					$result = $o->opt_txt;
					$cfans .= $result.', ';
				}
				$this->pdata->poll_results_msg_before = str_replace("{i".$q->q_id."}",$cfans,$this->pdata->poll_results_msg_before);
			}
		}
	}
	if ($this->pdata->poll_results_msg_before) echo $this->pdata->poll_results_msg_before;
	if ($this->pdata->poll_showresults) {
		foreach ($this->qdata as $q) {
			if ($q->q_type == "mcbox" || $q->q_type == "multi") {
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
							if ($this->resultsas == "percent") echo (int)($per*100)."%";
							else echo ($opts->anscount);
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

