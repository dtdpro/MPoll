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
				<?php echo JText::_( 'Public ID' ); ?>
            </th>
            <th>
				<?php echo JText::_( 'Status' ); ?>
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
				<?php echo $item->cm_pubid; ?>
                <?php if ($this->poll->poll_payment_enabled && $item->cm_status != "paid") { ?>
                    <br><small><a href="<?php echo JRoute::_(JUri::root().'index.php?option=com_mpoll&task=pay&poll='.$this->poll->poll_id. '&payment=' . base64_encode('cmplid='.$item->cm_id.'&id=' . $item->cm_pubid),false); ?>">Payment Link</a></small>
                <?php } ?>
            </td>
            <td>
				<?php echo $item->cm_status; ?>
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
						if (property_exists($item,$fn)) {
							echo $this->options[ $item->$fn ];
							if ( property_exists($item,$fno)) {
								echo ': ' . $item->$fno;
							}
						}
					}
					if ($qu->q_type == 'textbox' || $qu->q_type == 'mailchimp' || $qu->q_type == 'email' || $qu->q_type == 'datedropdown') {
						if (property_exists($item,$fn)) {
                            echo $item->$fn;
                        }
					}
					if ($qu->q_type == 'textar') {
						if (property_exists($item,$fn)) {
							echo nl2br( stripcslashes( $item->$fn ) );
						}
					}
					if ($qu->q_type == 'attach') {
						if (property_exists($item,$fn)) {
							if ( strpos( $item->$fn, "ERROR:" ) === false && $item->$fn != "" ) {
								$uploaded_files = explode( ",", $item->$fn );
								foreach ( $uploaded_files as $uf ) {
									echo 'Download: <a href="' . $uf . '">' . basename( $uf ) . '</a><br>';
								}
							} else {
								echo $item->$fn;
							}
						}
					}
					if ($qu->q_type == 'cbox') {
						if ( property_exists( $item, $fn ) ) {
							if ( $item->$fn ) {
								echo 'Yes';
							} else {
								echo 'No';
							}
						}
					}
					if ($qu->q_type == 'mcbox' || $qu->q_type=="mlist") {
						if (property_exists($item,$fn)) {
							$item->$fn = explode( " ", $item->$fn );
							foreach ( $item->$fn as $o ) {
								echo $this->options[ $o ] . '<br />';
							}
                            if ( property_exists($item,$fno)) {
                                echo 'Other: ' . $item->$fno;
                            }
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
