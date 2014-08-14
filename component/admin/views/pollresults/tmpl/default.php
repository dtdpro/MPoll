<?php 
defined('_JEXEC') or die('Restricted access'); 
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
?>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll&view=mpolls'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;
?>
		
<div id="editcell">

	<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
			</th>	
			<th>
				<?php echo JText::_( 'ID#' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Users Name' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Completed On' ); ?>
			</th>
            <?php 
				foreach ($this->questions as $qu) {
					echo '<th>#'.$qu->ordering.' '.$qu->q_text.'</th>';
				}
			?>
			<th>User Agent</th>
			<th>IP Address</th>
		</tr>			
	</thead>
	<?php
	$cq = 1;
	foreach ($this->items as $i => $item)
	{
		?>
		<tr class="row<?php echo $i % 2; ?>">
			<td><?php echo JHtml::_('grid.id', $i, $item->cm_id); ?></td>
			<td>
				<?php echo $item->cm_id; ?>
			</td>
			<td>
				<?php 
				if ($item->cm_user == 0) echo 'Guest';
				else echo $this->users[$item->cm_user]->name; ?>
			</td>
			<td>
				<?php echo $item->cm_time; ?>
			</td>
			<?php
            	foreach ($this->questions as $qu) {
            		$fn='q_'.$qu->q_id;
            		$fno='q_'.$qu->q_id.'_other';
					echo '<td>';
					$qnum = 'q'.$qu->q_id.'ans';
					if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown') { 
						echo $this->options[$item->$fn];
						if ($item->$fno) { echo ': '.$item->$fno; }
					}
					if ($qu->q_type == 'textbox' || $qu->q_type == 'mailchimp') { echo $item->$fn; }
					if ($qu->q_type == 'textar') { echo nl2br($item->$fn);; }
					if ($qu->q_type == 'attach') { 
						if (strpos($item->$fn,"ERROR:") === FALSE && $item->$fn != "") {
							echo '<a href="'.$i->$fn.'">Right Click Download</a>';
						} else {
							echo $item->$fn;
						}
					}
					if ($qu->q_type == 'email') { echo $item->$fn; }
					if ($qu->q_type == 'cbox') { if ($item->$fn) echo 'Yes'; else echo 'No'; }
					if ($qu->q_type == 'mcbox' || $qu->q_type=="mlist") {
						$item->$fn = explode(" ",$item->$fn);
						foreach ($item->$fn as $o) {
							echo $this->options[$o].'<br />';  
						}
					}
					echo '</td>';
				}
			?>
			<td><?php echo ($item->cm_useragent) ? $item->cm_useragent : "N/A"; ?></td>
			<td><?php echo ($item->cm_useragent) ? $item->cm_ipaddr : "N/A"; ?></td>
		</tr>
		<?php
	}
	?>
	</table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</div>
</form>
