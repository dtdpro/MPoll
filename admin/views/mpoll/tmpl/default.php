<?php defined('_JEXEC') or die('Restricted access'); 
$order = JHTML::_('grid.order', $this->items);
?>
<form action="" method="post" name="adminForm">

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
				<?php echo JText::_( 'Poll Name' ); ?>
			</th>

            <th width="50">
				<?php echo JText::_( 'Questions' ); ?>
			</th>
            <th width="50">
				<?php echo JText::_( '1 Vote Each' ); ?>
			</th>
            <th width="5">
				<?php echo JText::_( 'Published' ); ?>
			</th>
			<th width="150">
				<?php echo JText::_( 'Available' ); ?>
			</th>
			<th width="100">
				<?php echo JText::_( 'Results' ); ?>
			</th>
		</tr>			
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->poll_id );
		$published 	= JHTML::_('grid.published',   $row, $i  );
		$link 		= JRoute::_( 'index.php?option=com_mpoll&controller=mpolle&task=edit&cid[]='. $row->poll_id );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $row->poll_id; ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->poll_name; ?></a>
			</td>
            <td>
				<?php 
				
					echo '<a href="index.php?option=com_mpoll&view=question&q_poll='.$row->poll_id.'">Questions'; 
					$db =& JFactory::getDBO();
					$query = 'SELECT count(*) FROM #__mpoll_questions WHERE q_poll="'.$row->poll_id.'"';
					$db->setQuery( $query );
					echo ' ['.$db->loadResult().']</a>'; 
				
				?>
			</td>

			
            <td>
				<?php 
					if ($row->poll_only) echo 'Yes';
					else echo 'No'; 
				?>
			</td>
			<td>
				<?php echo $published; ?>
			</td>
			<td>
				<?php 
					if ($row->poll_start == '0000-00-00') echo 'Always';
					else echo date("M d, Y",strtotime($row->poll_start)).' - '.date("M d, Y",strtotime($row->poll_end)); 
				?>
			</td>
			<td>
				<?php 
					echo '<a href="index.php?option=com_mpoll&view=pollresults&poll='.$row->poll_id.'">By User</a>';
					echo ' | <a href="index.php?option=com_mpoll&view=tally&poll='.$row->poll_id.'">Tally</a>';
				?>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
    Helpful hint should go here.
</div>

<input type="hidden" name="option" value="com_mpoll" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="mpolle" />
</form>
