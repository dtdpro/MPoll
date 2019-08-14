<?php defined( '_JEXEC' ) or die( 'Restricted access' );
$db = JFactory::getDBO();
if ( ! empty( $this->sidebar ) ) : ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<?php else : ?>
    <div id="j-main-container">
		<?php endif;
		/*** DISPLAY POLL RESULTS ***/
		echo '<div class="componentheading">' . $this->pdata->poll_name . '</div>';

		foreach ( $this->qdata as $qr ) {
			if ( $qr->q_type == "mcbox" || $qr->q_type == "multi" || $qr->q_type == "dropdown" || $qr->q_type == "mlist" ) {
				echo '<div class="mpollcom-question">';
				$anscor = false;
				echo '<div class="mpollcom-question-text">' . $qr->q_text . '</div>';
				switch ( $qr->q_type ) {
					case 'multi':
					case 'mcbox':
					case 'mlist':
					case 'dropdown':
						$numr = 0;
						foreach ( $qr->options as &$o ) {
							$qa = 'SELECT count(*) FROM #__mpoll_results WHERE res_qid = ' . $qr->q_id . ' && res_ans LIKE "%' . $o->value . '%" GROUP BY res_qid';
							$db->setQuery( $qa );
							$o->anscount = $db->loadResult();
							if ( $o->anscount == "" ) {
								$o->anscount = 0;
							}
							$numr = $numr + (int) $o->anscount;
						}
						foreach ( $qr->options as $opts ) {
							if ( $opts->opt_selectable ) {
								if ( $numr != 0 ) {
									$per = ( $opts->anscount ) / ( $numr );
								} else {
									$per = 1;
								}
								echo '<div class="mpollcom-opt">';

								echo '<div class="mpollcom-opt-text">';
								if ( $opts->opt_correct ) {
									echo '<div class="mpollcom-opt-correct">' . $opts->text . '</div>';
								} else {
									echo $opts->text;
								}
								echo '</div>';

								echo '<div class="mpollcom-opt-count">';
								if ( $resultsas == "percent" ) {
									echo (int) ( $per * 100 ) . "%";
								} else {
									echo( $opts->anscount );
								}
								echo '</div>';

								echo '<div class="mpollcom-opt-bar-box"><div class="mpollcom-opt-bar-bar" style="background-color: ' . $opts->opt_color . '; width:' . ( $per * 100 ) . '%"></div></div>';

								echo '</div>';
							}
						}
						break;
					default:
						break;
				}
				echo '</div>';
			}
		}

		?>
    </div>