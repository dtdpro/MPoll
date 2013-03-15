<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'q.ordering';
$ordering	= ($listOrder == 'q.ordering');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll&view=questions'); ?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			
		</div>
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
			<select name="filter_poll" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_MPOLL_QUESTION_SELECT_POLL');?></option>
				<?php echo $html[] = JHtml::_('select.options',$this->polllist,"value","text",$this->state->get('filter.poll')); ?>
			</select>
		</div>
	</fieldset>
	
	<div class="clr"> </div>
	
	<table class="adminlist">
		<thead>
			<tr>
				<th width="5">
					<?php echo JText::_('COM_MPOLL_QUESTION_HEADING_ID'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>			
				<th>
					<?php echo JText::_('COM_MPOLL_QUESTION_HEADING_TITLE'); ?>
				</th>	
				<th width="100">
					<?php echo JText::_('JPUBLISHED'); ?>
				</th>	
				<th width="100">
					<?php echo JText::_( 'COM_MPOLL_QUESTION_HEADING_TYPE' ); ?>
				</th>
				<th width="100">
					<?php echo JText::_( 'COM_MPOLL_QUESTION_HEADING_REQ' ); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'q.ordering', $listDirn, $listOrder); ?>
					<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'questions.saveorder'); ?>
				</th>
				<th width="100">
					<?php echo JText::_( 'COM_MPOLL_QUESTION_HEADING_OPTS' ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody><?php 
		foreach($this->items as $i => $item):
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
					<td align="center">
						<?php switch ($item->q_type) {
							case "textar": echo 'Text Box'; break;
							case "textbox": echo 'Text Field'; break;
							case "email": echo 'EMail'; break;
							case "multi": echo 'Radio Select'; break;
							case "dropdown": echo 'Dropdown Select'; break;
							case "cbox": echo 'Check Box'; break;
							case "mcbox": echo 'Multi Checkbox'; break;
							case "attach": echo 'File Attachment'; break;
							case "message": echo 'Message'; break;
							case "header": echo 'Header'; break;
							case "captcha": echo 'Captcha'; break;
						} ?>
					</td>
					<td align="center">
						<?php echo ($item->q_req) ? "Yes" : "No"; ?>
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
			        <td align="center">
						<?php 
						if ($item->q_type=='multi' || $item->q_type=='mcbox' || $item->q_type=='dropdown') {
							echo '<a href="'.JRoute::_('index.php?option=com_mpoll&view=options&filter_question='.$item->q_id).'">Options'; 
							$db =& JFactory::getDBO();
							$query = 'SELECT count(*) FROM #__mpoll_questions_opts WHERE opt_qid="'.$item->q_id.'"';
							$db->setQuery( $query );
							echo ' ['.$db->loadResult().']</a>'; 
						}
					
					?>
					</td>
					
				</tr>
		<?php endforeach;
		
		?></tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


