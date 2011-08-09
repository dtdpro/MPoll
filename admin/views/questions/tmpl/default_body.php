<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->items as $i => $item): 
	$listOrder	= $this->escape($this->state->get('list.ordering'));
	$listDirn	= $this->escape($this->state->get('list.direction'));
	$saveOrder	= $listOrder == 'q.ordering';
	$ordering	= ($listOrder == 'q.ordering');
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $item->q_id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->q_id); ?>
		</td>
		<td>
				<a href="<?php echo JRoute::_('index.php?option=com_mpoll&task=question.edit&q_id='.(int) $item->q_id); ?>">
				<?php echo $this->escape($item->q_text); ?></a>
		</td>
		<td class="center">
			<?php echo JHtml::_('jgrid.published', $item->published, $i, 'questions.', true);?>
		</td>
		<td>
			<?php echo $item->q_type; ?>
		</td>
		<td>
			<?php echo $item->q_req; ?>
		</td>
        <td class="order">
				<?php if ($saveOrder) :?>
					<?php if ($listDirn == 'asc') : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, ($item->q_poll == @$this->items[$i-1]->q_poll), 'questions.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->q_poll == @$this->items[$i+1]->q_poll), 'questions.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
					<?php elseif ($listDirn == 'desc') : ?>
						<span><?php echo $this->pagination->orderUpIcon($i, ($item->q_poll == @$this->items[$i-1]->q_poll), 'questions.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->q_poll == @$this->items[$i+1]->q_poll), 'questions.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
					<?php endif; ?>
				<?php endif; ?>
				<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
				<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />

		</td>
        <td>
			<?php 
			if ($item->q_type=='multi' || $item->q_type=='mcbox') {
				echo '<a href="'.JRoute::_('index.php?option=com_mpoll&view=options&opt_question='.$item->q_id).'">Options'; 
				$db =& JFactory::getDBO();
				$query = 'SELECT count(*) FROM #__mpoll_questions_opts WHERE opt_qid="'.$item->q_id.'"';
				$db->setQuery( $query );
				echo ' ['.$db->loadResult().']</a>'; 
			}
		
		?>
		</td>
		
	</tr>
<?php endforeach; ?>

