<?php

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

$jinput = JFactory::getApplication()->input;
$function	= $jinput->getCmd('function', 'jSelectMPoll');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll'); ?>" method="post" name="adminForm" id="adminform">
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label class="element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_MPOLL_SEARCH_IN_TITLE'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_MPOLL_SEARCH_IN_TITLE'); ?>" />
		</div>
		<div class="btn-group pull-left">
			<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button class="btn hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		</div>
	</div>
	<hr class="hr-condensed" />
	<div class="filters pull-left">
		<select name="filter_published" class="input-medium" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
			<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
		</select>
		<select name="filter_category_id" class="input-medium" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
			<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_mpoll'), 'value', 'text', $this->state->get('filter.category_id'));?>
			</select>
            <select name="filter_access" class="input-medium" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
			<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
		</select>
	</div>

	<div class="clearfix"> </div>
	
	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th>
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_TITLE','poll_name', $listDirn, $listOrder); ?>
				</th>	
				<th width="20%">
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_CAT','category_title', $listDirn, $listOrder); ?>
				</th>	
				<th width="10%">
					<?php echo JHtml::_('grid.sort','JPUBLISHED','state', $listDirn, $listOrder); ?>
				</th>	
				<th width="15%">
					<?php echo JHtml::_('grid.sort','JGRID_HEADING_ACCESS','access_level', $listDirn, $listOrder); ?>
				</th>
				<th width="1%">
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_ID','poll_id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot><tr><td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
		<tbody>
		<?php foreach($this->items as $i => $item): 

			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
						<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->poll_id; ?>', '<?php echo $this->escape(addslashes($item->poll_name)); ?>');">
						<?php echo $this->escape($item->poll_name); ?></a>
				</td>
				<td class="small">
					<?php echo $item->category_title; ?>
				</td>
				<td class="small center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'mpolls.', true);?>
				</td>
				<td class="small">
					<?php echo $item->access_level; ?>
				</td>
				<td>
					<?php echo $item->poll_id; ?>
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