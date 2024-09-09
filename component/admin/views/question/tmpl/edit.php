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
	<div class="row-fluid row">
		<div class="span8 col-md-8">
            <div class="control-group ">
                <div class="control-label"><?php echo $this->form->getLabel('q_name'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('q_name'); ?></div>
            </div>
            <div class="control-group ">
                <div class="control-label"><?php echo $this->form->getLabel('q_type'); ?></div>
                <div class="controls"><?php echo $this->form->getInput('q_type'); ?></div>
            </div>
			<?php foreach($this->form->getFieldset('content') as $field): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label;?></div>
					<div class="controls"><?php echo $field->input;?></div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="span4 col-md-4 form-horizontal">
            <h4>Details</h4>
			<?php foreach($this->form->getFieldset('details') as $field): ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label;?></div>
					<div class="controls"><?php echo $field->input;?></div>
				</div>
			<?php endforeach; ?>
            <h4>Searchable Result Filtering</h4>
            <?php foreach($this->form->getFieldset('filtering') as $field): ?>
                <div class="control-group">
                    <div class="control-label"><?php echo $field->label;?></div>
                    <div class="controls"><?php echo $field->input;?></div>
                </div>
            <?php endforeach; ?>
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

