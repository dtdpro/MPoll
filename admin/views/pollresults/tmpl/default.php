<?php defined('_JEXEC') or die('Restricted access'); 
$db =& JFactory::getDBO();
?>
		
<div id="editcell">

	<table class="adminlist">
	<thead>
		<tr>
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
		</tr>			
	</thead>
	<?php
	$k = 0;
	$cq = 1;
	foreach ($this->items as $i)
	{
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php 
				if ($i->cm_user == 0) echo 'Guest';
				else echo $this->users[$i->cm_user]->name; ?>
			</td>
			<td>
				<?php echo $i->cm_time; ?>
			</td>
			<?php
            	foreach ($this->questions as $qu) {
            		$fn='q_'.$qu->q_id;
					echo '<td>';
					$qnum = 'q'.$qu->q_id.'ans';
					if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown') { 
						echo $this->options[$i->$fn];
					}
					if ($qu->q_type == 'textbox') { echo $i->$fn; }
					if ($qu->q_type == 'textar') { echo $i->$fn; }
					if ($qu->q_type == 'attach') { 
						if (strpos($i->$fn,"ERROR:") === FALSE && $i->$fn != "") {
							echo '<a href="'.$i->$fn.'">Right Click Download</a>';
						} else {
							echo $i->$fn;
						}
					}
					if ($qu->q_type == 'email') { echo $i->$fn; }
					if ($qu->q_type == 'cbox') { if ($i->$fn) echo 'Yes'; else echo 'No'; }
					if ($qu->q_type == 'mcbox' || $qu->q_type=="mlist") {
						$i->$fn = explode(" ",$i->$fn);
						foreach ($i->$fn as $o) {
							echo $this->options[$o].'<br />';  
						}
					}
					echo '</td>';
				}
			?>
			
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>

