<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

//JHtml::_('behavior.formvalidator');

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$params = $this->form->getFieldsets('params');

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

    <div class="row row-fluid title-alias form-vertical mb-3">
        <div class="col-12 col-md-6 span6">
		    <?php echo $this->form->renderField('poll_name'); ?>
        </div>
        <div class="col-12 col-md-6 span6">
		    <?php echo $this->form->renderField('poll_alias'); ?>
        </div>
    </div>

    <div class="form-horizontal main-card">

        <?php
	        echo HTMLHelper::_( 'uitab.startTabSet', 'myTab', array( 'active' => 'details', 'recall' => true, 'breakpoint' => 768 ) );
	        echo HTMLHelper::_('uitab.addTab', 'myTab', 'setup', Text::_('COM_MPOLL_MPOLL_SETUP'));
        ?>

            <div class="row-fluid row">
                <div class="span6 form-horizontal col-md-6">
                    <fieldset class="adminform">
                        <div class="form-grid">
                            <?php echo $this->form->renderFieldset('setup'); ?>
                        </div>
                    </fieldset>
                </div>
                <div class="span6 form-horizontal col-md-6">
                    <fieldset class="adminform">
                        <div class="form-grid">
                            <?php echo $this->form->renderFieldset('publishing'); ?>
                        </div>
                    </fieldset>
                </div>
            </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'results', Text::_('COM_MPOLL_MPOLL_RESULTS'));
        ?>

            <div class="row-fluid row">
                <div class="span10 form-horizontal col-md-10">
                    <?php echo $this->form->renderField('poll_resultsemail'); ?>
                    <h4>Email Results by Options</h4>
                    <p>Specify an email to go to a specific address based on an option selection. To set the reply to address use the field to the right. The all results email is separate and can be activated/deactivated by using the selector above.</p>
                    <?php echo $this->form->getInput('poll_results_emails'); ?>
                </div>
                <div class="form-vertical span2 col-md-2">
                    <fieldset class="adminform">
                        <div class="form-grid">
                            <?php echo $this->form->renderFieldset('results_all'); ?>
                        </div>
                    </fieldset>
                </div>
            </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'confirmation', Text::_('Confirmation'));
        ?>


            <div class="row-fluid row">

                <div class="span10 form-vertical col-md-10">
                    <?php echo $this->form->renderField('poll_confmsg'); ?>
                    <div style="clear:both">{name} Logged in users full name<br />{email} Logged in users email<br />{username} Logged in users username<br />{resid} Results id<br />{resurl} Results URL<br />{payurl} Pay URL
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
                    <fieldset class="adminform">
                        <div class="form-grid">
                            <?php echo $this->form->renderFieldset('confirmation'); ?>
                        </div>
                    </fieldset>
                </div>

            </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'payment', Text::_('Payment'));
        ?>

            <div class="row-fluid row">
                <div class="span10 form-horizontal col-md-10">
                    <fieldset class="adminform">
                        <div class="form-grid">
                            <?php echo $this->form->renderFieldset('paymentemail'); ?>
                        </div>
                    </fieldset>
                    <?php echo $this->form->renderField('poll_payment_body'); ?>
                </div>
                <div class="form-vertical span2 col-md-2">
                    <fieldset class="adminform">
                        <div class="form-grid">
                            <?php echo $this->form->renderFieldset('paymentdetails'); ?>
                        </div>
                    </fieldset>
                </div>
            </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'desc1', Text::_('COM_MPOLL_MPOLL_FIELD_DESC_LABEL'));
        ?>

            <p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_DESC_DESC'); ?></p>
            <div class="row-fluid form-horizontal-desktop row">
                <div class="form-vertical col-md-12">
                    <?php echo $this->form->renderField('poll_desc'); ?>
                </div>
            </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'desc2', Text::_('COM_MPOLL_MPOLL_FIELD_RMSGB_LABEL'));
        ?>

            <p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_RMSGB_DESC'); ?></p>
            <div class="row-fluid form-horizontal-desktop row">
                <div class="form-vertical col-md-12">
                    <?php echo $this->form->renderField('poll_results_msg_before'); ?>
                </div>
            </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'desc3', Text::_('COM_MPOLL_MPOLL_FIELD_RMSGA_LABEL'));
        ?>

            <p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_RMSGA_DESC'); ?></p>
            <div class="row-fluid form-horizontal-desktop row">
                <div class="form-vertical col-md-12">
                    <?php echo $this->form->renderField('poll_results_msg_after'); ?>
                </div>
            </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'desc4', Text::_('COM_MPOLL_MPOLL_FIELD_RMSGM_LABEL'));
        ?>

            <p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_RMSGM_DESC'); ?></p>
            <div class="row-fluid form-horizontal-desktop row">
                <div class="form-vertical col-md-12">
                    <?php echo $this->form->renderField('poll_results_msg_mod'); ?>
                </div>
            </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.addTab', 'myTab', 'searchresults', "Searchable Results");
        ?>

        <div class="row-fluid row">
            <div class="span10 form-vertical col-md-10">
                <fieldset class="adminform">
                    <div class="form-grid">
                        <?php echo $this->form->renderFieldset('searchableresults'); ?>
                    </div>
                </fieldset>
            </div>
            <div class="form-vertical span2 col-md-2">
                <fieldset class="adminform">
                    <div class="form-grid">
                        <?php echo $this->form->renderFieldset('searchableresultsdetails'); ?>
                    </div>
                </fieldset>
            </div>
        </div>

        <?php
            echo HTMLHelper::_('uitab.endTab');
            echo HTMLHelper::_('uitab.endTabSet');
        ?>
    </div>
	<div>
		<input type="hidden" name="task" value="mpoll.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


