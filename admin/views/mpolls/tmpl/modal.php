<?php

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

$function	= JRequest::getCmd('function', 'jSelectNewsfeed');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll&view=mpolls&layout=modal&tmpl=component');?>" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th class="title">
					Title
				</th>
				<th width="1%" class="nowrap">
					ID
				</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->poll_id; ?>', '<?php echo $this->escape(addslashes($item->poll_name)); ?>');">
						<?php echo $this->escape($item->poll_name); ?></a>
				</td>
				<td align="center">
					<?php echo (int) $item->poll_id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
