<?php

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'option.cancel' || document.formvalidator.isValid(document.id('mpoll-form'))) {
			Joomla.submitform(task, document.getElementById('mpoll-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll&layout=edit&opt_id='.(int) $this->item->opt_id); ?>" method="post" name="adminForm" id="mpoll-form" class="form-validate">
<div class="row-fluid">	
<div class="width-60 fltlft span12">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MPOLL_OPTION_DETAILS' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('details') as $field): ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
			</ul>
		</fieldset>

	</div>

		<input type="hidden" name="task" value="option.edit" />
		<?php echo JHtml::_('form.token'); ?>
</div>
</form>

