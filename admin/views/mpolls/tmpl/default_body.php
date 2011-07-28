<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->items as $i => $item): 

	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $item->poll_id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->poll_id); ?>
		</td>
		<td>
				<a href="<?php echo JRoute::_('index.php?option=com_mpoll&task=mpoll.edit&id='.(int) $item->poll_id); ?>">
				<?php echo $this->escape($item->poll_name); ?></a>
			<p class="smallsub"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->poll_alias));?></p>
		</td>
		<td>
			<?php echo $item->category_title; ?>
		</td>
		<td class="center">
			<?php echo JHtml::_('jgrid.published', $item->published, $i, 'mpolls.', true);?>
		</td>
		<td>
			<?php echo $item->access_level; ?>
		</td>
        <td>
			<?php 
		
			echo '<a href="'.JRoute::_('index.php?option=com_mpoll&view=question&q_poll='.$item->poll_id).'">Questions'; 
			$db =& JFactory::getDBO();
			$query = 'SELECT count(*) FROM #__mpoll_questions WHERE q_poll="'.$item->poll_id.'"';
			$db->setQuery( $query );
			echo ' ['.$db->loadResult().']</a>'; 
		
		?>
		</td>
		<td>
			<?php 
				if ($item->poll_start == '0000-00-00 00:00:00') echo 'Always';
				else { 
					echo 'B: '.date("M d, Y",strtotime($item->poll_start)).'<br />E: '.date("M d, Y",strtotime($item->poll_end)); 
				}
			?>
		</td>
		<td>
			<?php 
				echo '<a href="'.JRoute::_('index.php?option=com_mpoll&view=pollresults&poll='.$item->poll_id).'">By User</a>';
				echo ' | <a href="'.JRoute::_('index.php?option=com_mpoll&view=tally&poll='.$i->poll_id).'">Tally</a>';
			?>
		</td>
	</tr>
<?php endforeach; ?>

