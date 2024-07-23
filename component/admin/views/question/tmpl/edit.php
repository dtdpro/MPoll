<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
$params = $this->form->getFieldsets('params');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'question.cancel' || document.formvalidator.isValid(document.getElementById('mpoll-form'))) {
			Joomla.submitform(task, document.getElementById('mpoll-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mpoll&layout=edit&q_id='.(int) $this->item->q_id); ?>" method="post" name="adminForm" id="mpoll-form" class="form-validate">
    <div class="form-horizontal main-card">

        <?php
        echo HTMLHelper::_( 'uitab.startTabSet', 'myTab', array( 'active' => 'details', 'recall' => true, 'breakpoint' => 768 ) );
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'setup', 'Details');
        ?>
	<div class="form-inline form-inline-header">
		<div class="control-group ">
			<div class="control-label"><?php echo $this->form->getLabel('q_name'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('q_name'); ?></div>
		</div>
		<div class="control-group ">
			<div class="control-label"><?php echo $this->form->getLabel('q_type'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('q_type'); ?></div>
		</div>
	</div>
	<div class="row-fluid <?php if (JVersion::MAJOR_VERSION >= 4) { ?>row<?php } ?>">
		<div class="span8 col-md-8">
			<?php foreach($this->form->getFieldset('content') as $field): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label;?></div>
					<div class="controls"><?php echo $field->input;?></div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="span4 col-md-4 form-horizontal">
			<?php foreach($this->form->getFieldset('details') as $field): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label;?></div>
					<div class="controls"><?php echo $field->input;?></div>
				</div>
			<?php endforeach; ?>
			<?php 
				if ($this->item->q_id) {
					if ($this->item->q_type == "mailchimp") {
						foreach ($this->form->getFieldset("mailchimp") as $field) : ?>
							<div class="control-group">
								<div class="control-label"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
						<?php endforeach; ?>
						
						<?php
						foreach ($this->item->list_mvars as $v) {
							if ($v['tag'] != "EMAIL") {
								$tag=$v['tag'];
								echo '<div class="control-group">';
								echo '<div class="control-label">'.$v['name'].'</div>';
								echo '<div class="controls">';
								echo '<select name="jform[params][mcvars]['.$v['tag'].']" id="jform_'.$v['tag'].'" class="inputbox">';
								echo '<option value="">None</option>';
								echo JHtml::_('select.options', $this->item->questions, 'value', 'text', $this->item->params['mcvars'][$tag], true);
								echo '</select>';
								echo '</div>';
								echo '</div>';
							}
						}
					}
				}
			?>
		</div>
	</div>
        <?php
        echo HTMLHelper::_('uitab.endTab');
        echo HTMLHelper::_('uitab.endTabSet');
        ?>
    </div>

		<input type="hidden" name="task" value="question.edit" />
		<?php echo JHtml::_('form.token'); ?>


</form>

