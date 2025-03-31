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
        if (task == 'pollresult.cancelemail' || document.formvalidator.isValid(document.getElementById('mpoll-form'))) {
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
                <div class="control-group ">
                    <div class="control-label">Email Template</div>
                    <div class="controls"><?php

                        echo '<select name="templateId" id="jform_template" class="form-select">';
                        foreach ($this->availableTemplates as $template) {
                            echo '<option value="'.$template->tmpl_id.'">'.$template->tmpl_name.'</option>';
                        }
                        echo '</select>';

                        ?></div>
                </div>

            </div>
            <div class="width-50 fltlft span6 col-md-6">
                <h3>Send to:</h3>
                <?php
                    foreach ($this->questions as $question) {
                        if ($question->q_type == 'email'|| $question->q_type == 'textbox' || $question->q_type == 'textar') {
                            $fn = 'q_'.$question->q_id;
                            echo '<strong>'.$question->q_text.'</strong>: ';
                            echo $this->item->$fn.'<br>';
                        }
                    }

                ?>
            </div>
        </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_( 'uitab.endTabSet' );
        ?>
        </div>

    <input type="hidden" name="task" value="pollresult.sendemail" />
    <input type="hidden" name="id" value="<?php echo $this->item->cm_id; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>

