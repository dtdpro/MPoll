<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?><tr>
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
		<?php echo JText::_( 'COM_MPOLL_OPTION_HEADING_CORRECT' ); ?>
	</th>
	<th width="50">
		<?php echo JText::_( 'COM_MPOLL_OPTION_HEADING_OTHER' ); ?>
	</th>
	<th width="10%">
		<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'o.ordering', $listDirn, $listOrder); ?>
		<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'options.saveorder'); ?>
	</th>
				


</tr>

