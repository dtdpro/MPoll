<?php defined('_JEXEC') or die('Restricted access'); 
$order = JHTML::_('grid.order', $this->items);
?>
<form action="index.php" method="post" name="adminForm">
<input type="hidden" name="opt_qid" value="<?php echo $this->questionid; ?>">
<input type="hidden" name="q_poll" value="<?php echo $this->pollid; ?>">
<p>Poll: <b><?php echo $this->pollinfo->poll_name; ?></b><br />
Question: <b>#<?php echo $this->qinfo->ordering.' '.$this->qinfo->q_text; ?></b></p>
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'id' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>			
			<th>
				<?php echo JText::_( 'Option Text' ); ?>
			</th>
            <th width="75">
				<?php echo JText::_( 'Order' ).$order; ?>
			</th>
		</tr>			
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->opt_id );
		$link 		= JRoute::_( 'index.php?option=com_mpoll&controller=answere&task=edit&q_poll='.$this->pollid.'&opt_qid='.$this->questionid.'&cid[]='. $row->opt_id );
	
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $row->opt_id; ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->opt_txt; ?></a>
			</td>
			<td class="order">
				<span><?php echo $this->pagination->orderUpIcon( $i, true,'orderup','Move Up'); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $n, true,'orderdown','Move Down'); ?></span>
				<input size="3" type="text" name="order[]" style="text-align:center;" value="<?php echo $row->ordering; ?>" <?php if($this->filter_part) echo 'disabled="true"'; ?>/>
			</td>
			 
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	<tfoot>
		<td colspan="4">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>
</div>

<input type="hidden" name="option" value="com_mpoll" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="answere" />
</form>
