<?php defined('_JEXEC') or die('Restricted access'); 
$db =& JFactory::getDBO();
?>
		
<div id="editcell">

	<table class="adminlist">
	<thead>
		<tr>
			<th>
				<?php echo JText::_( 'User' ); ?>
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
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php 
				if ($row['cm_user'] == 0) echo 'Guest';
				else echo $row['username']; ?>
			</td>
			<td>
				<?php echo $row['cm_time']; ?>
			</td>
			<?php
            	foreach ($this->questions as $qu) {
					echo '<td>';
					$qnum = 'q'.$qu->q_id.'ans';
					if ($qu->q_type == 'multi' || $qu->q_type == 'dropdown') { 
						if ($row[$qnum.'o']) echo '<em>Other:</em> '.$row[$qnum.'o'];
						else echo $row[$qnum];
					}
					if ($qu->q_type == 'textbox') { echo $row[$qnum]; }
					if ($qu->q_type == 'textar') { echo $row[$qnum]; }
					if ($qu->q_type == 'cbox') { if ($row[$qnum] == 'on') echo 'Checked'; else echo 'Unchecked'; }
					if ($qu->q_type == 'mcbox') {
						$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$qu->q_id.' ORDER BY ordering ASC';
						$db->setQuery( $query );
						$qopts = $db->loadAssocList();
						$answers = explode(' ',$row[$qnum]);
						foreach ($qopts as $opts) {
							if (in_array($opts['opt_id'],$answers)) { 
								if ($opts['opt_other']) echo '<em>Other:</em> '.$row[$qnum.'o'].'<br />';
								else echo $opts['opt_txt'].'<br />'; 
							} 
						}
					}
					echo '</td>';
				}
			?>
			
		</tr>
		<?php
		$k = 1 - $k;
		$cq = $row->disporder+1;
	}
	?>
	</table>
</div>

