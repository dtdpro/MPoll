<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('bootstrap.tooltip');
//JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
//JHtml::_('formbehavior.chosen', 'select');
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$app	= JFactory::getApplication();
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$published = $this->state->get('filter.published');
$saveOrder = ($listOrder == 'q.ordering');
$sortFields = $this->getSortFields();
if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_mpoll&task=questions.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'MPollQuestionList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
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

<form action="<?php echo JRoute::_('index.php?option=com_mpoll&view=questions'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
        <div class="alert alert-info" role="alert">
            <?php echo '<strong>Poll:</strong> '.$this->polltitle; ?>
        </div>
	<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>
	
	<table class="adminlist table table-striped" id ="MPollQuestionList">
		<thead>
			<tr>
				<th width="1%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>	
				<th width="1%" style="min-width:55px" class="nowrap center">
					<?php echo JText::_('JSTATUS'); ?>
				</th>			
				<th>
					<?php echo JText::_('COM_MPOLL_QUESTION_HEADING_TITLE'); ?>
				</th>	
				<th width="5%">
					<?php echo JText::_( 'COM_MPOLL_QUESTION_HEADING_REQ' ); ?>
				</th>
                <th width="5%">
                    <?php echo JText::_( 'COM_MPOLL_QUESTION_HEADING_HIDDEN' ); ?>
                </th>
                <th width="5%">
                    <?php echo JText::_( 'COM_MPOLL_QUESTION_HEADING_FILTER' ); ?>
                </th>
				<th width="1%">
					<?php echo JText::_('COM_MPOLL_QUESTION_HEADING_ID'); ?>
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
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="pollquestions">
					<td class="order nowrap center text-center hidden-phone">
						<?php 
						$disableClassName = '';
						$disabledLabel	  = '';
						if (!$saveOrder) :
							$disabledLabel    = JText::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
	
					</td>
					<td>
						<?php echo JHtml::_('grid.id', $i, $item->q_id); ?>
					</td>
					<td class="center text-center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->published, $i, 'questions.', true);?>
							<?php

							    if ( JVersion::MAJOR_VERSION == 3 ) {
								    echo JHtml::_( 'mpolladministrator.options', $i, $item->q_type, true );
								    // Create dropdown items

								    if ( $item->published ) :
									    JHtml::_( 'actionsdropdown.unpublish', 'cb' . $i, 'questions' );
								    else :
									    JHtml::_( 'actionsdropdown.publish', 'cb' . $i, 'questions' );
								    endif;

								    JHtml::_( 'actionsdropdown.divider' );

								    if ( $trashed ) :
									    JHtml::_( 'actionsdropdown.untrash', 'cb' . $i, 'questions' );
								    else :
									    JHtml::_( 'actionsdropdown.trash', 'cb' . $i, 'questions' );
								    endif;

								    // Render dropdown list
								    echo JHtml::_( 'actionsdropdown.render' );
							    }
							?>
						</div>
					</td>
					<td>
							<a href="<?php echo JRoute::_('index.php?option=com_mpoll&task=question.edit&q_id='.(int) $item->q_id); ?>">
							<?php echo $this->escape($item->q_name); ?></a>
							<div class="small">
								<strong>Type:</strong> 
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
									case "mlist": echo 'Multi Select'; break;
									case "mailchimp": echo 'Mailchimp List'; break;
                                    case "datedropdown": echo 'Date Dropdown'; break;
                                    case "gmap": echo "Google Maps Address"; break;
								} ?>
								<?php 
									if ($item->q_type=='mlist' ||$item->q_type=='multi' || $item->q_type=='mcbox' || $item->q_type=='dropdown') {
										echo ' | <strong>Options:</strong> '.$item->options;
								        if ( JVersion::MAJOR_VERSION >= 4 ) {
                                            echo '<br>';
									        echo JHtml::_( 'mpolladministrator.options', $i, $item->q_type, true );
								        }
									}
								?>
							</div>
					</td>
					<td class="small center text-center">
						<?php echo ($item->q_req) ? '<span style="color:#008000">Yes</span>' : '<span style="color:#800000">No</span>'; ?>
					</td>
                    <td class="small center text-center">
                        <?php echo ($item->q_hidden) ? '<span style="color:#008000">Yes</span>' : '<span style="color:#800000">No</span>'; ?>
                    </td>
                    <td class="small center text-center">
                        <?php echo ($item->q_filter) ? '<span style="color:#008000">Yes</span>' : '<span style="color:#800000">No</span>'; ?>
                    </td>
					<td>
						<?php echo $item->q_id; ?>
					</td>
				</tr>
		<?php endforeach;
		
		?></tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>


