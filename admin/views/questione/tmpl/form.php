<?php defined('_JEXEC') or die('Restricted access'); 
$qtypes[0]=JHTML::_('select.option','multi','Radio Select');
$qtypes[1]=JHTML::_('select.option','textbox','Text Field');
$qtypes[2]=JHTML::_('select.option','cbox','Checkbox Single');
$qtypes[3]=JHTML::_('select.option','mcbox','Multi Checkbox');
$qtypes[4]=JHTML::_('select.option','textar','Text Box');
$qtypes[5]=JHTML::_('select.option','dropdown','Drop Down');

$ctypes[0]=JHTML::_('select.option','bar','Old Bar Chart');
$ctypes[1]=JHTML::_('select.option','barg','Google Bar Chart');
$ctypes[2]=JHTML::_('select.option','pieg','Google Pie Chart');



?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="q_poll" value="<?php echo $this->pollid; ?>">

<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Question' ); ?>:
				</label>
			</td>
			<td>
				<textarea class="text_area" name="q_text" id="q_text" cols="60" rows="2"><?php echo $this->question->q_text;?></textarea>
			</td></tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Question #' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->question->ordering; ?>
                <input type="hidden" name="ordering" value="<?php echo $this->question->ordering; ?>">
			</td></tr>
    <tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Type' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.genericlist',$qtypes,'q_type',NULL,'value','text',$this->question->q_type); ?>
			</td></tr>
			    <tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Chart Type' ); ?>:
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.genericlist',$ctypes,'q_charttype',NULL,'value','text',$this->question->q_charttype); ?>
			</td></tr>
    <tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Required' ); ?>:
				</label>
			</td>
			<td>
            	<?php echo JHTML::_('select.booleanlist','q_req','',$this->question->q_req,'Yes','No','q_req'); ?>
			</td></tr>
   	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_mpoll" />
<input type="hidden" name="q_id" value="<?php echo $this->question->q_id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="questione" />
</form>
