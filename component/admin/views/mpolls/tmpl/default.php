<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$app	= JFactory::getApplication();
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$published = $this->state->get('filter.published');
$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mpoll&view=mpolls'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label class="element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_MPOLL_SEARCH_IN_TITLE'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_MPOLL_SEARCH_IN_TITLE'); ?>" />
		</div>
		<div class="btn-group pull-left">
			<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button class="btn hasTooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		</div>	
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
			<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
				<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
				<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
			<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
				<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
			</select>
		</div>
	</div>
	
	<div class="clearfix"> </div>
	
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>	
				<th width="1%" style="min-width:55px" class="nowrap center">
					<?php echo JHtml::_('grid.sort','JSTATUS','p.published', $listDirn, $listOrder); ?>
				</th>		
				<th>
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_TITLE','p.poll_name', $listDirn, $listOrder); ?>
				</th>		
				<th width="10%">
					<?php echo JText::_('JCATEGORY'); ?>
				</th>		
				<th width="8%">
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_PTYPE','p.poll_type', $listDirn, $listOrder); ?>
				</th>	
				<th width="5%">
					<?php echo JText::_('COM_MPOLL_MPOLL_HEADING_REGREQ'); ?>
				</th>	
				<th width="8%">
					<?php echo JText::_( 'COM_MPOLL_MPOLL_HEADING_AVAILABILITY' ); ?>
				</th>		
				<th width="10%" class="hidden-phone">
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_ADDED','p.created', $listDirn, $listOrder); ?>
				</th>		
				<th width="10%" class="hidden-phone">
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_MODIFIED','p.modified', $listDirn, $listOrder); ?>
				</th>	
				<th width="5%">
					<?php echo JHtml::_('grid.sort','JGRID_HEADING_ACCESS','p.access', $listDirn, $listOrder); ?>
				</th>
				<th width="1%">
					<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_ID','p.poll_id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		
		<tfoot><tr><td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
		<tbody>
		<?php foreach($this->items as $i => $item): ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td><?php echo JHtml::_('grid.id', $i, $item->poll_id); ?></td>
				<td class="center">
					<div class="btn-group">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'products.', true); ?>
						<?php echo JHtml::_('mpolladministrator.questions',$i, true); ?>
						<?php
							// Create dropdown items
							
							if ($item->state) :
								JHtml::_('actionsdropdown.unpublish', 'cb' . $i, 'articles');
							else :
								JHtml::_('actionsdropdown.publish', 'cb' . $i, 'articles');
							endif;
							
							JHtml::_('actionsdropdown.divider');
							
							if ($trashed) :
								JHtml::_('actionsdropdown.untrash', 'cb' . $i, 'articles');
							else :
								JHtml::_('actionsdropdown.trash', 'cb' . $i, 'articles');
							endif;
							
							// Render dropdown list
							echo JHtml::_('actionsdropdown.render');
						?>
					</div>
				</td>
				<td class="nowrap has-context">
					<div class="pull-left">
						<a href="<?php echo JRoute::_('index.php?option=com_mpoll&task=mpoll.edit&poll_id=' . $item->poll_id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?>">
							<?php echo $this->escape($item->poll_name); ?>
						</a>
						<span class="small">
							<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->poll_alias));?>
						</span>
						<div class="small">
						 	<strong>Questions:</strong> 
						 	<?php echo $item->questions; ?> 
						 	| <strong>Submissions:</strong> 
						 	<?php 
						 		echo $item->results; 
						 		if ($item->results) {
							 		echo ' | <a href="'.JRoute::_('index.php?option=com_mpoll&view=pollresults&poll='.$item->poll_id).'">Results</a>';
									echo ' | <a href="'.JRoute::_('index.php?option=com_mpoll&view=tally&poll='.$item->poll_id).'">Tally</a>';
								}
							?>
						</div>
					</div>
				</td>
				<td class="small"><?php echo $item->category_title; ?></td>
				<td class="small">
					<?php 
						switch ($item->poll_pagetype) {
							case "poll": echo "Poll"; break;
							case "form": echo "Form"; break;
						} 
					?>
				</td>
				<td class="small">
					<?php
						if ($item->poll_regreq) echo '<span style="color:#008000">Yes</span>';
						else echo '<span style="color:#800000">No</span>';
					?>
				</td>
				<td class="small">
					<?php 
						if ($item->poll_start == '0000-00-00 00:00:00') echo 'Always';
						else { 
							echo 'B: '.$item->poll_start.'<br />E: '.$item->poll_end; 
						}
					?>
				</td>
				<td class="small hidden-phone"><?php echo $item->poll_created.'<br />'.$item->adder; ?></td>
				<td class="small hidden-phone"><?php echo $item->poll_modified.'<br />'.$item->modifier; ?></td>
				<td class="small"><?php echo $item->access_level; ?></td>
				<td><?php echo $item->poll_id; ?></td>
				
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</div>
</form>


