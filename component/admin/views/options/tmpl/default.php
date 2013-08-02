<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');


$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'o.ordering';
$ordering	= ($listOrder == 'o.ordering');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll&view=options'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft pull-left">
			
		</div>
		<div class="filter-select fltrt pull-right">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
			<select name="filter_question" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_MPOLL_OPTION_SELECT_QUESTION');?></option>
				<?php echo $html[] = JHtml::_('select.options',$this->qlist,"value","text",$this->state->get('filter.question')); ?>
			</select>
		</div>
	</fieldset>
	
	<div class="clr clearfix"> </div>
	
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="5">
					<?php echo JText::_('COM_MPOLL_OPTION_HEADING_ID'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>			
				<th>
					<?php echo JText::_('COM_MPOLL_OPTION_HEADING_TITLE'); ?>
				</th>	
				<th width="100">
					<?php echo JText::_('JPUBLISHED'); ?>
				</th>	
				<th width="50">
					<?php echo JText::_( 'COM_MPOLL_OPTION_HEADING_SELECTABLE' ); ?>
				</th>
				<th width="50">
					<?php echo JText::_( 'COM_MPOLL_OPTION_HEADING_DISABLED' ); ?>
				</th>
				<th width="50">
					<?php echo JText::_( 'COM_MPOLL_OPTION_HEADING_CORRECT' ); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'o.ordering', $listDirn, $listOrder); ?>
					<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'options.saveorder'); ?>
				</th>
			</tr>
		</thead>
		<tfoot><tr><td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
		<tbody>
		<?php foreach($this->items as $i => $item):	?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo $item->opt_id; ?>
					</td>
					<td>
						<?php echo JHtml::_('grid.id', $i, $item->opt_id); ?>
					</td>
					<td>
							<a href="<?php echo JRoute::_('index.php?option=com_mpoll&task=option.edit&opt_id='.(int) $item->opt_id); ?>">
							<?php echo $this->escape($item->opt_txt); ?></a>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'options.', true);?>
					</td>
					<td align="center">
						<?php echo ($item->opt_selectable) ? '<span style="color:#008000">Yes</span>' : '<span style="color:#800000">No</span>'; ?>
					</td>
					<td align="center">
						<?php echo ($item->opt_disabled) ? '<span style="color:#008000">Yes</span>' : '<span style="color:#800000">No</span>'; ?>
					</td>
			       <td align="center">
						<?php echo ($item->opt_correct) ? '<span style="color:#008000">Yes</span>' : '<span style="color:#800000">No</span>'; ?>
					</td>
			        <td class="order">
							<?php if ($saveOrder) :?>
								<?php if ($listDirn == 'asc') : ?>
									<span><?php echo $this->pagination->orderUpIcon($i, ($item->opt_qid == @$this->items[$i-1]->opt_qid), 'options.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
									<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->opt_qid == @$this->items[$i+1]->opt_qid), 'options.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
								<?php elseif ($listDirn == 'desc') : ?>
									<span><?php echo $this->pagination->orderUpIcon($i, ($item->opt_qid == @$this->items[$i-1]->opt_qid), 'options.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
									<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->opt_qid == @$this->items[$i+1]->opt_qid), 'options.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
								<?php endif; ?>
							<?php endif; ?>
							<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
							<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
			
					</td>
				
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


