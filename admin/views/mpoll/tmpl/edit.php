<?php

// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll&layout=edit&poll_id='.(int) $this->item->poll_id); ?>" method="post" name="adminForm" id="mpoll-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MPOLL_MPOLL_SETUP' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('setup') as $field): ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
			</ul>
		</fieldset>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_MPOLL_MPOLL_DETAILS' ); ?></legend>
<?php foreach($this->form->getFieldset('details') as $field): ?>
				<?php echo '<div>'.$field->label.'<div class="clr"></div>'.$field->input.'</div>';?>
<?php endforeach; ?>
		</fieldset>
	</div>

	<div class="width-40 fltrt">
		<?php echo JHtml::_('sliders.start', 'mpollrev-slider'); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_MPOLL_MPOLL_PUBLISHING'), 'publishing-details'); ?>
		<fieldset class="panelform">
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('publishing') as $field): ?>
				<li><?php echo $field->label;echo $field->input;?></li>
<?php endforeach; ?>
			</ul>
		</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<div>
		<input type="hidden" name="task" value="mpoll.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

