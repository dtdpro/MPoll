<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'pollresult.cancel' || document.formvalidator.isValid(document.getElementById('mpoll-form'))) {
            Joomla.submitform(task, document.getElementById('mpoll-form'));
        }
    }

</script>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll&layout=edit&id='.(int) $this->item->cm_id); ?>" method="post" name="adminForm" id="mpoll-form" class="form-validate">
    <div class="form-horizontal main-card">
        <?php
            echo HTMLHelper::_('uitab.startTabSet', 'myTab', array( 'active' => 'details', 'recall' => true, 'breakpoint' => 768 ) );
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', 'Submission Details');
        ?>

        <div class="row-fluid row">
            <div class="width-50 fltlft span6 col-md-6">
                <fieldset class="adminform form-horizontal">
                    <?php foreach($this->questions as $f) {
                        $sname = 'q_'.$f->q_id;

                        echo '<div class="control-group">';

                        //field title
                        echo '<div class="control-label">';
                        echo '<label id="jform_'.$sname.'-lbl" for="jform_'.$sname.'" class="hasTip" title="'.$f->q_name.'::">'.$f->q_text.'</label>';
                        echo '</div>';
                        echo '<div class="controls">';

                        //multi checkbox
                        if ($f->q_type=="mcbox" || $f->q_type=="mlist") {
                            echo '<fieldset id="jform_'.$sname.'" class="radio inputbox">';
                            foreach ($f->options as $o) {
                                if (!empty($this->item->$sname)) $checked = in_array($o->value,$this->item->$sname) ? ' checked="checked"' : '';
                                else $checked = '';
                                echo '<input type="checkbox" name="jform['.$sname.'][]" value="'.$o->value.'" id="jform_'.$sname.$o->value.'"'.$checked.'/>'."\n";
                                echo '<label for="jform_'.$sname.$o->value.'">';
                                echo ' '.$o->text.'</label><br /><br />'."\n";

                            }
                            echo '</fieldset>';
                        }

                        //dropdown, radio
                        if ($f->q_type=="multi" || $f->q_type=="dropdown") {
                            echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-select inputbox" size="1">';
                            foreach ($f->options as $o) {
                                if (!empty($this->item->$sname)) $selected = ($o->value == $this->item->$sname) ? ' selected="selected"' : '';
                                else $selected = '';
                                echo '<option value="'.$o->value.'"'.$selected.'>';
                                echo ' '.$o->text.'</option>';
                            }
                            echo '</select>';
                        }

                        //text field, phone #, email, username, birthday
                        if ($f->q_type=="textbox" || $f->q_type=="email" || $f->q_type=="gmap") {
                            echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$this->item->$sname.'" class="form-control inputbox" size="70" type="text">';
                        }

                        //password
                        if ($f->q_type=="password") {
                            echo '<input name="jform['.$sname.']" id="jform_'.$sname.'" value="'.$this->item->$sname.'" class="form-control inputbox" size="20" type="password">';
                        }

                        //text area
                        if ($f->q_type=="textar") {
                            echo '<textarea name="jform['.$sname.']" id="jform_'.$sname.'" cols="70" rows="4" class="form-control inputbox">'.$this->item->$sname.'</textarea>';
                        }

                        //Yes no
                        if ($f->q_type=="cbox") {
                            echo '<select id="jform_'.$sname.'" name="jform['.$sname.']" class="form-select inputbox" size="1">';
                            $selected = ' selected="selected"';
                            echo '<option value="0"';
                            echo ($this->item->$sname == "0") ? $selected : '';
                            echo '>No</option>';
                            echo '<option value="1"';
                            echo ($this->item->$sname == "1") ? $selected : '';
                            echo '>Yes</option>';
                            echo '</select>';
                        }

                        //password
                        if ($f->q_type=="attach") {
                            echo 'User editable only';
                        }

                        // Date Dropdown
                        if ( $f->q_type == 'datedropdown' ) {
                            $value = $f->value;
                            $selected = ' selected="selected"';
                            $html = "";
                            $html .= '<select id="jform_' . $sname . '_month" name="jform[' . $sname . '_month]" class="form-select inputbox">';
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

                            $html .= '<select id="jform_'.$sname.'_day" name="jform[' . $sname . '_day]" class="form-select inputbox">';
                            for ($i=1;$i<=31;$i++) {
                                if ($i<10) $val = "0".$i;
                                else $val=$i;
                                $html .= '<option value="'.$val.'"';
                                $html .= (substr($value,3,2) == $val) ? $selected : '';
                                $html .= '>'.$val.'</option>';
                            }
                            $html .=  '</select>';

                            $html .= '<select id="jform_'.$sname.'_year" name="jform[' . $sname . '_year]" class="form-select inputbox">';
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

                        echo '</div>';
                        echo '</div>';
                    } ?>

                </fieldset>
            </div>
        </div>

        <?php


            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_( 'uitab.endTabSet' );
        ?>
        </div>

    <input type="hidden" name="task" value="pollresult.edit" />
    <input type="hidden" name="cm_id" value="<?php echo $this->item->cm_id; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>

