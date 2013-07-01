<?php

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

$function	= JRequest::getCmd('function', 'jSelectNewsfeed');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll'); ?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="Search" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
			<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_mpoll'), 'value', 'text', $this->state->get('filter.category_id'));?>
			</select>
            <select name="filter_access" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>
		</div>
	</fieldset>
	<div class="clr"> </div>
	
	<table class="adminlist">
		<thead>
			<tr>
				<th width="5">
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_ID','poll_id', $listDirn, $listOrder); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>			
				<th>
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_TITLE','poll_name', $listDirn, $listOrder); ?>
				</th>	
				<th width="200">
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_CAT','category_title', $listDirn, $listOrder); ?>
				</th>	
				<th width="100">
					<?php echo JHtml::_('grid.sort','JPUBLISHED','state', $listDirn, $listOrder); ?>
				</th>	
				<th width="100">
					<?php echo JHtml::_('grid.sort','JGRID_HEADING_ACCESS','access_level', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot><tr><td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
		<tbody>
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
						<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->poll_id; ?>', '<?php echo $this->escape(addslashes($item->poll_name)); ?>');">
						<?php echo $this->escape($item->poll_name); ?></a>
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