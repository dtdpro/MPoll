<?php

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidator');
$params = $this->form->getFieldsets('params');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>




<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'mpoll.cancel' || document.formvalidator.isValid(document.getElementById('mpoll-form'))) {
			Joomla.submitform(task, document.getElementById('mpoll-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mpoll&layout=edit&poll_id='.(int) $this->item->poll_id); ?>" method="post" name="adminForm" id="mpoll-form" class="form-validate">

    <div class="<?php if (JVersion::MAJOR_VERSION == 3) { ?>form-inline form-inline-header <?php } ?>row row-fluid title-alias form-vertical mb-3">
        <div class="col-12 col-md-6 span6">
		    <?php echo $this->form->renderField('poll_name'); ?>
        </div>
        <div class="col-12 col-md-6 span6">
		    <?php echo $this->form->renderField('poll_alias'); ?>
        </div>
    </div>

    <div class="form-horizontal main-card">

        <?php
        if (JVersion::MAJOR_VERSION == 4) {
	        echo HTMLHelper::_( 'uitab.startTabSet', 'myTab', array( 'active' => 'details', 'recall' => true, 'breakpoint' => 768 ) );
	        echo HTMLHelper::_('uitab.addTab', 'myTab', 'setup', Text::_('COM_MPOLL_MPOLL_SETUP'));
        } else {
	        echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'setup'));
	        echo JHtml::_('bootstrap.addTab', 'myTab', 'setup', JText::_('COM_MPOLL_MPOLL_SETUP', true));
        }
        ?>

            <div class="row-fluid <?php if (JVersion::MAJOR_VERSION == 4) { ?>row<?php } ?>">
                <div class="span6 form-horizontal col-md-6">
                    <?php foreach($this->form->getFieldset('setup') as $field): ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $field->label;?></div>
                            <div class="controls"><?php echo $field->input;?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="span6 form-horizontal col-md-6">
                    <?php foreach($this->form->getFieldset('publishing') as $field): ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $field->label;?></div>
                            <div class="controls"><?php echo $field->input;?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php
        if (JVersion::MAJOR_VERSION == 4) {
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'results', Text::_('COM_MPOLL_MPOLL_RESULTS'));
        } else {
            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.addTab', 'myTab', 'results', JText::_('COM_MPOLL_MPOLL_RESULTS', true));
        }
        ?>

            <div class="row-fluid <?php if (JVersion::MAJOR_VERSION == 4) { ?>row<?php } ?>">
                <div class="span10 form-horizontal col-md-10">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('poll_resultsemail');?></div>
                        <div class="controls"><?php echo $this->form->getInput('poll_resultsemail');?></div>
                    </div>
                    <h4>Email Results by Options</h4>
                    <p>Specify an email to go to a specific address based on an option selection. To set the reply to address use the field to the right. The all results email is separate and can be activated/deactivated by using the selector above.</p>
                    <?php echo $this->form->getInput('poll_results_emails'); ?>
                </div>
                <div class="form-vertical span2 col-md-2">
                    <?php foreach($this->form->getFieldset('results_all') as $field): ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $field->label;?></div>
                            <div class="controls"><?php echo $field->input;?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php
        if (JVersion::MAJOR_VERSION == 4) {
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'confirmation', Text::_('Confirmation'));
        } else {
            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.addTab', 'myTab', 'confirmation', JText::_('Confirmation', true));
        }
        ?>


            <div class="row-fluid <?php if (JVersion::MAJOR_VERSION == 4) { ?>row<?php } ?>">

                <div class="span10 form-vertical col-md-10">
                    <?php echo $this->form->renderField('poll_confmsg'); ?>
                    <div style="clear:both">{name} Users full name<br />{email} Users email<br />{username} Users username<br />{resid} Results id<br />{resurl} Results URL<br />{payurl} Pay URL
                        <?php
                        if ($this->item->poll_id) {
                            foreach ($this->questions as $q) {
                                echo '<br />{i'.$q->value.'} '.$q->text;
                            }
                        }

                        ?>
                    </div>
                </div>

                <div class="form-vertical span2 col-md-2">
                    <?php foreach($this->form->getFieldset('confirmation') as $field): ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $field->label;?></div>
                            <div class="controls"><?php echo $field->input;?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

        <?php
        if (JVersion::MAJOR_VERSION == 4) {
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'payment', Text::_('Payment'));
        } else {
            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.addTab', 'myTab', 'payment', JText::_('Payment', true));
        }
        ?>

            <div class="row-fluid <?php if (JVersion::MAJOR_VERSION == 4) { ?>row<?php } ?>">
                <div class="span10 form-horizontal col-md-10">
                    <?php foreach($this->form->getFieldset('paymentemail') as $field): ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $field->label;?></div>
                            <div class="controls"><?php echo $field->input;?></div>
                        </div>
                    <?php endforeach; ?>
                    <?php echo $this->form->renderField('poll_payment_body'); ?>
                </div>
                <div class="form-vertical span2 col-md-2">
                    <?php foreach($this->form->getFieldset('paymentdetails') as $field): ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $field->label;?></div>
                            <div class="controls"><?php echo $field->input;?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php
        if (JVersion::MAJOR_VERSION == 4) {
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'desc1', Text::_('COM_MPOLL_MPOLL_FIELD_DESC_LABEL'));
        } else {
            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.addTab', 'myTab', 'desc1', JText::_('COM_MPOLL_MPOLL_FIELD_DESC_LABEL', true));
        }
        ?>

            <p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_DESC_DESC'); ?></p>
            <div class="row-fluid form-horizontal-desktop <?php if (JVersion::MAJOR_VERSION == 4) { ?>row<?php } ?>">
                <div class="form-vertical col-md-12">
                    <?php echo $this->form->renderField('poll_desc'); ?>
                </div>
            </div>

        <?php
        if (JVersion::MAJOR_VERSION == 4) {
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'desc2', Text::_('COM_MPOLL_MPOLL_FIELD_RMSGB_LABEL'));
        } else {
            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.addTab', 'myTab', 'desc2', JText::_('COM_MPOLL_MPOLL_FIELD_RMSGB_LABEL', true));
        }
        ?>

            <p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_RMSGB_DESC'); ?></p>
            <div class="row-fluid form-horizontal-desktop <?php if (JVersion::MAJOR_VERSION == 4) { ?>row<?php } ?>">
                <div class="form-vertical col-md-12">
                    <?php echo $this->form->renderField('poll_results_msg_before'); ?>
                </div>
            </div>

        <?php
        if (JVersion::MAJOR_VERSION == 4) {
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'desc3', Text::_('COM_MPOLL_MPOLL_FIELD_RMSGA_LABEL'));
        } else {
            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.addTab', 'myTab', 'desc3', JText::_('COM_MPOLL_MPOLL_FIELD_RMSGA_LABEL', true));
        }
        ?>

            <p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_RMSGA_DESC'); ?></p>
            <div class="row-fluid form-horizontal-desktop <?php if (JVersion::MAJOR_VERSION == 4) { ?>row<?php } ?>">
                <div class="form-vertical col-md-12">
                    <?php echo $this->form->renderField('poll_results_msg_after'); ?>
                </div>
            </div>

        <?php
        if (JVersion::MAJOR_VERSION == 4) {
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'desc4', Text::_('COM_MPOLL_MPOLL_FIELD_RMSGM_LABEL'));
        } else {
            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.addTab', 'myTab', 'desc4', JText::_('COM_MPOLL_MPOLL_FIELD_RMSGM_LABEL', true));
        }
        ?>

            <p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_RMSGM_DESC'); ?></p>
            <div class="row-fluid form-horizontal-desktop <?php if (JVersion::MAJOR_VERSION == 4) { ?>row<?php } ?>">
                <div class="form-vertical col-md-12">
                    <?php echo $this->form->renderField('poll_results_msg_mod'); ?>
                </div>
            </div>


        <?php
        if (JVersion::MAJOR_VERSION == 4) {
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.endTabSet');
        } else {
            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.endTabSet');
        }
        ?>
    </div>
	<div>
		<input type="hidden" name="task" value="mpoll.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


