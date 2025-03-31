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

<form action="<?php echo JRoute::_('index.php?option=com_mpoll&view=emialtemplates'); ?>" method="post" name="adminForm" id="adminForm">

	<div id="j-main-container">

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
				<th width="20">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>	
				<th width="1%" style="min-width:55px" class="nowrap center">
					<?php echo JText::_('JSTATUS'); ?>
				</th>			
				<th>Name</th>
				<th>Subject</th>
				<th width="1%">ID</th>
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
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo JHtml::_('grid.id', $i, $item->tmpl_id); ?>
					</td>
					<td class="center text-center">
                        <?php
                        $options = [ 'task_prefix' => 'questions.', 'id' => 'state-' . $item->tmpl_id ];
                        echo ( new PublishedButton() )->render( (int) $item->published, $i, $options );
                        ?>
					</td>
					<td>
							<a href="<?php echo JRoute::_('index.php?option=com_mpoll&task=emailtemplate.edit&tmpl_id='.(int) $item->tmpl_id); ?>">
							<?php echo $this->escape($item->tmpl_name); ?></a>
					</td>
					<td class="">
						<?php echo $item->tmpl_subject; ?>
                    </td>
					<td>
						<?php echo $item->tmpl_id; ?>
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


