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
		if (task == 'option.cancel' || document.formvalidator.isValid(document.getElementById('mpoll-form'))) {
			Joomla.submitform(task, document.getElementById('mpoll-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mpoll&layout=edit&opt_id='.(int) $this->item->opt_id); ?>" method="post" name="adminForm" id="mpoll-form" class="form-validate">
    <div class="form-horizontal main-card">

        <?php
        echo HTMLHelper::_('uitab.startTabSet', 'myTab', array( 'active' => 'details', 'recall' => true, 'breakpoint' => 768 ) );
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'setup', 'Details');
        ?>
        <div class="row-fluid">
            <div class="span12 form-horizontal">
                <?php foreach($this->form->getFieldset('details') as $field): ?>
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

    <input type="hidden" name="task" value="option.edit" />
    <?php echo JHtml::_('form.token'); ?>

</form>

