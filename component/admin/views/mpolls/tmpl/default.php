<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Button\FeaturedButton;
use Joomla\CMS\Button\PublishedButton;

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
	<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>
	
	<div class="clearfix"> </div>
	
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
                <th width="1%" style="min-width:55px" class="nowrap center">
					<?php echo JHtml::_('searchtools.sort','JSTATUS','p.published', $listDirn, $listOrder); ?>
				</th>		
				<th>
					<?php echo JHtml::_('searchtools.sort','COM_MPOLL_MPOLL_HEADING_TITLE','p.poll_name', $listDirn, $listOrder); ?>
				</th>		
				<th width="10%">
					<?php echo JText::_('JCATEGORY'); ?>
				</th>	
				<th width="10%">
					<?php echo JText::_( 'COM_MPOLL_MPOLL_HEADING_AVAILABILITY' ); ?>
				</th>		
				<th width="10%" class="hidden-phone">
					<?php echo JHtml::_('searchtools.sort','COM_MPOLL_MPOLL_HEADING_ADDED','p.poll_created', $listDirn, $listOrder); ?>
				</th>		
				<th width="10%" class="hidden-phone">
					<?php echo JText::_('COM_MPOLL_MPOLL_HEADING_MODIFIED'); ?>
				</th>	
				<th width="5%">
					<?php echo JHtml::_('searchtools.sort','JGRID_HEADING_ACCESS','p.access', $listDirn, $listOrder); ?>
				</th>
				<th width="1%">
					<?php echo JHtml::_('searchtools.sort','COM_MPOLL_MPOLL_HEADING_ID','p.poll_id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		
		<tfoot><tr><td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td></tr></tfoot>
		<tbody>
		<?php foreach($this->items as $i => $item): ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td><?php echo JHtml::_('grid.id', $i, $item->poll_id); ?></td>
                <td class="center text-center">
                    <?php
                    $options = [ 'task_prefix' => 'mpolls.', 'id' => 'state-' . $item->poll_id ];
                    echo ( new PublishedButton() )->render( (int) $item->published, $i, $options );
                    ?>
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
						 	<strong>Type:</strong> 
							<?php 
								switch ($item->poll_pagetype) {
									case "poll": echo "Poll"; break;
									case "form": echo "Form"; break;
								} 
							?>
						 	 | <strong>Questions:</strong>
						 	<?php echo $item->questions; ?>  
						 	<?php if ($item->poll_regreq) echo ' | <span style="color:#800000">Reg Required</span>'; ?>
						 	| <strong>Submissions:</strong>
						 	<?php 
						 		echo $item->results.'<br>';
                                echo JHtml::_('mpolladministrator.questions',$i, true);
                                echo '&nbsp;' . JHtml::_('mpolladministrator.emailtemplates',$i, true, $item->emailFields);
                                echo '&nbsp;' . JHtml::_('mpolladministrator.results',$i, true, $item->results);
								echo '&nbsp;' . JHtml::_('mpolladministrator.tally',$i, true, $item->results);
							?>
						</div>
					</div>
				</td>
				<td class="small"><?php echo $item->category_title; ?></td>
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


		<?php
		echo '<h3>Webhook URL</h3>';
		echo '<p>'.JUri::root().'index.php?option=com_mpoll&task=paypal_webhook&view=mpoll'.'</p>';
		?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
</div>
</form>
