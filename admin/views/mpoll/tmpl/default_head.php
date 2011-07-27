<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?><tr>
	<th width="5">
		<?php echo JText::_('COM_MPOLL_MPOLL_HEADING_ID'); ?>
	</th>
	<th width="20">
		<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>			
	<th>
		<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_TITLE','p.poll_title', $listDirn, $listOrder); ?>
	</th>	
	<th width="200">
		<?php echo JHtml::_('grid.sort','COM_MPOLL_MPOLL_HEADING_CAT','category_title', $listDirn, $listOrder); ?>
	</th>	
	<th width="100">
		<?php echo JHtml::_('grid.sort','JPUBLISHED','p.state', $listDirn, $listOrder); ?>
	</th>	
	<th width="100">
		<?php echo JHtml::_('grid.sort','JGRID_HEADING_ACCESS','p.access', $listDirn, $listOrder); ?>
	</th>
	<th width="50">
		<?php echo JText::_( 'COM_MPOLL_MPOLL_HEADING_QUESTIONS' ); ?>
	</th>
	<th width="150">
		<?php echo JText::_( 'COM_MPOLL_MPOLL_HEADING_AVAILABILITY' ); ?>
	</th>
	<th width="100">
		<?php echo JText::_( 'COM_MPOLL_MPOLL_HEADING_RESULTS' ); ?>
	</th>
</tr>

