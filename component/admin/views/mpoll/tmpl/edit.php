<?php

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');
$params = $this->form->getFieldsets('params');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'product.cancel' || document.formvalidator.isValid(document.id('mpoll-form'))) {
			Joomla.submitform(task, document.getElementById('mpoll-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mpoll&layout=edit&poll_id='.(int) $this->item->poll_id); ?>" method="post" name="adminForm" id="mpoll-form" class="form-validate">
	<div class="form-inline form-inline-header">
		<div class="control-group ">
			<div class="control-label"><?php echo $this->form->getLabel('poll_name'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('poll_name'); ?></div>
		</div>
		<div class="control-group ">
			<div class="control-label"><?php echo $this->form->getLabel('poll_alias'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('poll_alias'); ?></div>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'setup')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'setup', JText::_('COM_MPOLL_MPOLL_SETUP', true)); ?>
			<div class="row-fluid">		
				<div class="span6 form-horizontal">
					<?php foreach($this->form->getFieldset('setup') as $field): ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label;?></div>
							<div class="controls"><?php echo $field->input;?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="span6 form-horizontal">
					<?php foreach($this->form->getFieldset('publishing') as $field): ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label;?></div>
							<div class="controls"><?php echo $field->input;?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'results', JText::_('COM_MPOLL_MPOLL_RESULTS', true)); ?>
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<?php foreach($this->form->getFieldset('confirmation') as $field): ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label;?></div>
							<div class="controls"><?php echo $field->input;?></div>
						</div>
					<?php endforeach; ?>
					<div class="control-group">
						<?php echo $this->form->getLabel('poll_confmsg').$this->form->getInput('poll_confmsg'); ?>
						<div style="clear:both">{name} Users full name<br />{email} Users email<br />{username} Users username<br />{resid} Results id
							<?php 
								if ($this->item->poll_id) {
									foreach ($this->questions as $q) {
										echo '<br />{i'.$q->value.'} '.$q->text;
									}
								}
							
							?>
						</div>
					</div>
				</div>
				<div class="span2">
					<?php foreach($this->form->getFieldset('results') as $field): ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label;?></div>
							<div class="controls"><?php echo $field->input;?></div>
						</div>
					<?php endforeach; ?>
				</div>	
			</div>				
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'desc1', JText::_('COM_MPOLL_MPOLL_FIELD_DESC_LABEL', true)); ?>
			<p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_DESC_DESC'); ?></p>
			<div class="control-group">
				<?php echo $this->form->getInput('poll_desc'); ?>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'desc2', JText::_('COM_MPOLL_MPOLL_FIELD_RMSGB_LABEL', true)); ?>
			<p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_RMSGB_DESC'); ?></p>
			<div class="control-group">
				<?php echo $this->form->getInput('poll_results_msg_before'); ?>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'desc3', JText::_('COM_MPOLL_MPOLL_FIELD_RMSGA_LABEL', true)); ?>
			<p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_RMSGA_DESC'); ?></p>
			<div class="control-group">
				<?php echo $this->form->getInput('poll_results_msg_after'); ?>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'desc4', JText::_('COM_MPOLL_MPOLL_FIELD_RMSGM_LABEL', true)); ?>
			<p><?php echo jText::_('COM_MPOLL_MPOLL_FIELD_RMSGM_DESC'); ?></p>
			<div class="control-group">
				<?php echo $this->form->getInput('poll_results_msg_mod'); ?>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	<div>
		<input type="hidden" name="task" value="mpoll.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


