<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Button\FeaturedButton;


?>
<form action="<?php echo JRoute::_('index.php?option=com_mpoll&view=pollresults'); ?>" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container">
    <div class="alert alert-info" role="alert">
        <?php echo '<strong>Poll:</strong> '.$this->polltitle; ?>
    </div>

    <?php
    // Search tools bar
    echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
    ?>

	<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
			</th>
            <th width="1%" style="min-width:55px" class="nowrap center">
                <?php echo JText::_('JSTATUS'); ?>
            </th>
            <th width="1%" style="min-width:55px" class="nowrap center">
                <?php echo JText::_('JFEATURED'); ?>
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
            <td class="center text-center">
                <?php
                $options = [ 'task_prefix' => 'pollresults.', 'id' => 'state-' . $item->cm_id ];
                echo ( new PublishedButton() )->render( (int) $item->published, $i, $options );
                ?>
            </td>
            <td class="center text-center">
                <?php
                $options = [ 'task_prefix' => 'pollresults.', 'id' => 'featured-' . $item->cm_id ];
                echo ( new FeaturedButton() )->render( (int) $item->featured, $i, $options );
                ?>
            </td>
			<td>
                <a href="<?php echo JRoute::_('index.php?option=com_mpoll&task=pollresult.edit&cm_id='.(int) $item->cm_id); ?>"><?php echo $item->cm_id; ?></a>
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
					if ($qu->q_type == 'textbox' || $qu->q_type == 'mailchimp' || $qu->q_type == 'email' || $qu->q_type == 'datedropdown' || $qu->q_type == 'gmap') {
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
                                if ($item->$fno) echo 'Other: ' . $item->$fno;
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

</form>
