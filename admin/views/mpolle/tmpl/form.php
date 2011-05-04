<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Published' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist','published','',$this->polle->published,'Yes','No','published'); ?>
			</td></tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Poll Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="poll_name" id="poll_name" size="70" maxlength="255" value="<?php echo $this->polle->poll_name;?>" />
			</td></tr>

        <tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Poll Description' ); ?>:
				</label>
			</td>
			<td>
				<textarea class="text_area" name="poll_desc" id="poll_desc" cols="60" rows="2"><?php echo $this->polle->poll_desc;?></textarea>
			</td></tr>
            <tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Results Message' ); ?>:
				</label>
			</td>
			<td>
				<textarea class="text_area" name="poll_rmsg" id="poll_rmsg" cols="60" rows="2"><?php echo $this->polle->poll_rmsg;?></textarea>
			</td></tr>
    <tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Poll Open' ); ?>:
				</label>
			</td>
			<td>
				From: <?php echo JHTML::_('calendar',$this->polle->poll_start,'poll_start','poll_start','%Y-%m-%d',null); ?> Enter in 0000-00-00 for constant availability<br>
				To: <?php echo JHTML::_('calendar',$this->polle->poll_end,'poll_end','poll_end','%Y-%m-%d',null); ?>
			</td></tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( '1 Vote Each' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist','poll_only','',$this->polle->poll_only,'Yes','No','poll_only'); ?>
			</td></tr>
            		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Registered Only?' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist','poll_regonly','',$this->polle->poll_regonly,'Yes','No','poll_regonly'); ?>
			</td></tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Show Results' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist','poll_showresults','',$this->polle->poll_showresults,'Yes','No','poll_showresults'); ?>
			</td></tr>
    	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_mpoll" />
<input type="hidden" name="poll_id" value="<?php echo $this->polle->poll_id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="mpolle" />
</form>
