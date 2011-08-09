<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
?><tr>
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

