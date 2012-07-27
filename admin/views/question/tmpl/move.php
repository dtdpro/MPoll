<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
		function submitbutton(pressbutton) {
			var form = document.adminForm;
			if (pressbutton == 'cancel') {
				submitform( pressbutton );
				return;
			}

			// do field validation
			if (!getSelectedValue( 'adminForm', 'newpoll' )) {
				alert( "<?php echo JText::_( 'Please select a poll from the list', true ); ?>" );
			} else {
				submitform( pressbutton );
			}
		}
		</script>

<form action="index.php" method="post" name="adminForm">
	<table class="adminform">
		<tr>
			<td width="3%"></td>
			<td  valign="top" width="30%">
			<strong><?php echo JText::_( 'Move to Poll' ); ?>:</strong>
			<br />
			<?php echo $this->polllist ?>
			<br /><br />
			</td>
			<td  valign="top">
			<strong>
			<?php echo JText::_( 'Questions being moved' ); ?>:
			</strong>
			<br />
			<ol>
				<?php foreach ( $this->qus as $qu ) : ?>
				<li><?php echo $qu->q_text; ?></li>
				<?php endforeach; ?>
			</ol>
			</td>
		</tr>
	</table>

	<input type="hidden" name="option" value="com_mpoll" />
	<input type="hidden" name="boxchecked" value="1" />
	<input type="hidden" name="controller" value="questione" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="q_poll" value="<?php echo $this->pollid; ?>" />
<?php foreach ( $this->qus as $qu ) : ?>
	<input type="hidden" name="cid[]" value="<?php echo $qu->q_id; ?>" />
<?php endforeach; ?>

</form>
