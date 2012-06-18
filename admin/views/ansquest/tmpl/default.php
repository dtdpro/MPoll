<?php defined('_JEXEC') or die('Restricted access'); 
$db =& JFactory::getDBO();
?>
		
<div id="editcell">
<?php echo '<b>'.JText::_('Question: ').'</b>'.$this->data->q_text; ?>
	<table class="adminlist">
	<thead>
		<tr>
			<th>
				<?php echo JText::_( 'Results' ); ?>
			</th>
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
				if ($this->data->q_type == 'multi') { echo $row->opt_txt.' - '.$row->tally; }
				if ($this->data->q_type == 'textbox') { echo $row->res_ans; }
				if ($this->data->q_type == 'textar') { echo $row->res_ans; }
				if ($this->data->q_type == 'cbox') { if ($row->res_ans == 'on') echo 'Checked'; else echo 'Unchecked'; }
				if ($this->data->q_type == 'mcbox') {
					$query = 'SELECT * FROM #__mpoll_questions_opts WHERE opt_qid = '.$row->res_qid.' ORDER BY opt_order ASC';
					$db->setQuery( $query );
					$qopts = $db->loadAssocList();
					$answers = explode(' ',$row->res_ans);
					foreach ($qopts as $opts) {
						if (in_array($opts['id'],$answers)) { echo $opts['opt_txt'].'<br />'; } 
					}
				}
				?>
			</td>
			
		</tr>
		<?php
		$k = 1 - $k;
		$cq = $row->disporder+1;
	}
	?>
	</table>
</div>

