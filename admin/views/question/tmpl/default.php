<?php defined('_JEXEC') or die('Restricted access'); 
$order = JHTML::_('grid.order', $this->items);
?>
<form action="index.php" method="post" name="adminForm">
<input type="hidden" name="q_poll" value="<?php echo $this->pollid; ?>">
<p>Poll: <b><?php echo $this->pollinfo->poll_name; ?></b></p>
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
				<?php echo JText::_( 'Question' ); ?>
			</th>
            <th width="100">
				<?php echo JText::_( 'Order' ).$order; ?>
			</th>
            <th width="100">
				<?php echo JText::_( 'Type' ); ?>
			</th>
            <th width="100">
				<?php echo JText::_( 'Chart' ); ?>
			</th>
            <th width="50">
				<?php echo JText::_( 'Required' ); ?>
			</th>
            <th width="50">
				<?php echo JText::_( 'Options' ); ?>
			</th>
            <th width="50">
				<?php echo JText::_( 'Responses' ); ?>
			</th>
		</tr>			
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->q_id );
		$published 	= JHTML::_('grid.published',   $row, $i,'tick.png','publish_x.png', 'req'  );
		$link 		= JRoute::_( 'index.php?option=com_mpoll&controller=questione&task=edit&q_poll='.$this->pollid.'&cid[]='. $row->q_id );
	
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $row->q_id; ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->q_text; ?></a>
			</td>
            <td class="order">
				<span><?php echo $this->pagination->orderUpIcon( $i, true,'orderup','Move Up'); ?></span>
				<span><?php echo $this->pagination->orderDownIcon( $i, $n, true,'orderdown','Move Down'); ?></span><input size="3" type="text" name="order[]" style="text-align:center;" value="<?php echo $row->ordering; ?>" <?php if($this->filter_part) echo 'disabled="true"'; ?>/>
			</td>
			 <td>
				<?php 
				if ($row->q_type == 'multi') echo 'Radio Select'; 
				if ($row->q_type == 'textbox') echo 'Text Field'; 
				if ($row->q_type == 'cbox') echo 'Checkbox'; 
				if ($row->q_type == 'mcbox') echo 'Multi Checkbox'; 
				if ($row->q_type == 'textar') echo 'Text Box'; 
				if ($row->q_type == 'dropdown') echo 'Drop Down'; 
				
				?>
			</td>
			 <td>
				<?php 
				if ($row->q_charttype == 'bar') echo 'Old Bar Chart'; 
				if ($row->q_charttype == 'barg') echo 'Bar Chart'; 
				if ($row->q_charttype == 'pieg') echo 'Pie Chart'; 
				
				?>
			</td>
            <td>
				<?php echo $published; ?>
			</td>
            <td><?php if ($row->q_type == 'multi' || $row->q_type == 'mcbox' || $row->q_type == 'dropdown') { ?><a href="index.php?option=com_mpoll&view=answer&q_poll=<?php echo $this->pollid; ?>&opt_qid=<?php echo $row->q_id; ?>">Options<?php
			$db =& JFactory::getDBO();
			$query = 'SELECT count(*) FROM #__mpoll_questions_opts WHERE opt_qid="'.$row->q_id.'"';
		$db->setQuery( $query );
		echo ' ['.$db->loadResult().']'; 
		
			 
			 }
			?></a></td>
            <td><a href="index.php?option=com_mpoll&view=ansquest&q_poll=<?php echo $this->pollid; ?>&opt_qid=<?php echo $row->q_id; ?>">Responses</a></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	<tfoot>
		<td colspan="10">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>
</div>

<input type="hidden" name="option" value="com_mpoll" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="questione" />
</form>
