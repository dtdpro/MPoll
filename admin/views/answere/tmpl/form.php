<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="opt_qid" value="<?php echo $this->questionid; ?>">
<input type="hidden" name="q_poll" value="<?php echo $this->pollid; ?>">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Option' ); ?>:
				</label>
			</td>
			<td>
				<textarea class="text_area" name="opt_txt" id="opt_txt" cols="60" rows="2"><?php echo $this->answer->opt_txt;?></textarea>
			</td></tr>
		
    <tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Order' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->answer->ordering;?>
                <input type="hidden" name="ordering" value="<?php echo $this->answer->ordering; ?>">
			</td></tr>
			    <tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Other box' ); ?>:
				</label>
			</td>
			<td>
            	<?php echo JHTML::_('select.booleanlist','opt_other','',$this->answer->opt_other,'Yes','No','opt_other'); ?><br>For Radio Select and Multi Checkbox only, only 1 per question
			</td></tr>
			<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Correct' ); ?>:
				</label>
			</td>
			<td>
            	<?php echo JHTML::_('select.booleanlist','opt_correct','',$this->answer->opt_correct,'Yes','No','opt_correct'); ?>
			</td></tr>
   	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_mpoll" />
<input type="hidden" name="opt_id" value="<?php echo $this->answer->opt_id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="answere" />
</form>
