<?php

jimport( 'joomla.application.component.view');


class MPollViewMPoll extends JViewLegacy
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$model = $this->getModel();
		$doc = JFactory::getDocument();
		$output = null;
		$jinput = JFactory::getApplication()->input;

		$this->params = $app->getParams();
		$this->pollid = $jinput->getInt( 'poll' );
		$this->task = $jinput->getVar('task');

		// Load Config
		$this->cfg = MPollHelper::getConfig();

		// When not a paypal webnhook
		if ($this->task != "paypal_webhook") {
			// Check for poll id
			if (!$this->pollid){
				throw new \Exception("Not Found", 404);
			}

			// Get Poll based on id
			$this->pdata = $model->getPoll( $this->pollid );
			$this->ended = false;
			$this->started = true;

			// Check if poll exists
			if ( empty( $this->pdata ) ) {
				throw new \Exception("Not Found", 404);
				return false;
			}

			// When poll is not serachable and search task, kick out
			if ( !$this->pdata->poll_results_searchable && $this->task == "search") {
				throw new \Exception("Not Found", 404);
				return false;
			}

			// Check Availablity - Start
			if ( ( strtotime( $this->pdata->poll_start ) > strtotime( date( "Y-m-d H:i:s" ) ) ) && $this->pdata->poll_start != '0000-00-00 00:00:00' ) {
				$this->started=false;
				if ( ! $this->pdata->poll_shownotstarted ) {
					throw new \Exception(JText::_('COM_MPOLL_POLL_NOT_AVAILABLE'), 404);
					return false;
				}
			}


			// Check Availablity - End
			if ( ( strtotime( $this->pdata->poll_end ) < strtotime( date( "Y-m-d H:i:s" ) ) ) && $this->pdata->poll_start != '0000-00-00 00:00:00' ) {
				$this->ended = true;
				if ( ! $this->pdata->poll_showended ) {
					throw new \Exception(JText::_('COM_MPOLL_POLL_NOT_AVAILABLE'), 404);
					return false;
				}
			}

			//Check for previous submission when only 1 submission allowed
			if ( $this->pdata->poll_only && $user->id ) {
				if ( $model->getCasted( $this->pollid ) ) {
					$this->task = 'results';
				}
			}

			$this->_prepareTitle();
		}

		switch ($this->task) {
			case "paypal_webhook":
				$output = $model->PayPalWebhook();
				break;
			case "paypal_create": // cretae paypal payment
			case "paypal_execute": // excute paypal payment
			case "paypal_cancel": // cancel paypal payment
			case "paypal_cancel_link": //cancel payppal paymnet url
			case "paypal_create_sub": // Create paypal sub
			case "paypal_activate_sub": // Activate sub after payment
			case "pay": // Pay screen
				$this->payment = $jinput->getVar('payment');
				$this->payurl = JRoute::_('index.php?option=com_mpoll&view=mpoll&task=pay&poll='.$this->pollid. '&payment=' . $this->payment);
				$paydetails = array();
				parse_str(base64_decode($this->payment),$paydetails);
				$this->completition = $model->getCompletition((int)$paydetails['cmplid'],$paydetails['id']);
				if (!$this->completition) {
					$url = 'index.php?option=com_mpoll&view=mpoll&task=results&poll=' . $this->pollid .'&cmpl=' . $this->payment;
					$app->redirect(JRoute::_($url,false));
				} else {
					if (in_array($this->completition->cm_status,array('complete','paid','refunded','approved'))) {
						$url = 'index.php?option=com_mpoll&view=mpoll&task=results&poll=' . $this->pollid . '&cmpl=' . $this->payment;
						$app->redirect(JRoute::_($url,false));
					}
					else {
						if ($this->task == 'paypal_create') {
							if (!$output = $model->PayPalCreate($this->pdata,$this->completition)) {
								$url = 'index.php?option=com_mpoll&view=mpoll&task=pay&poll=' . $this->pollid .'&payment=' . $this->payment;
								if ( $this->params->get( 'rtmpl', '' ) ) {
									$url .= '&tmpl=' . $rtmpl;
								}
								$app->enqueueMessage($model->getError(), 'error');
								$app->redirect(JRoute::_($url,false));
							}
						}
						if ($this->task == 'paypal_create_sub') {
							if (!$output = $model->PayPalCreateSub($this->pdata,$this->completition)) {
								$url = 'index.php?option=com_mpoll&view=mpoll&task=pay&poll=' . $this->pollid .'&payment=' . $this->payment;
								if ( $this->params->get( 'rtmpl', '' ) ) {
									$url .= '&tmpl=' . $rtmpl;
								}
								$app->enqueueMessage($model->getError(), 'error');
								$app->redirect(JRoute::_($url,false));
							}
						}
						if ($this->task == 'paypal_execute') {
							if (!$model->PayPalExecute($this->pdata,$this->completition)) {
								$url = 'index.php?option=com_mpoll&view=mpoll&task=pay&poll=' . $this->pollid .'&payment=' . $this->payment;
								if ( $this->params->get( 'rtmpl', '' ) ) {
									$url .= '&tmpl=' . $rtmpl;
								}
								$app->enqueueMessage($model->getError(), 'error');
								$app->redirect(JRoute::_($url,false));
							}
							$url = 'index.php?option=com_mpoll&view=mpoll&task=results&poll=' . $this->pollid . '&cmpl=' . $this->payment;
							$app->redirect(JRoute::_($url,false));
						}
						if ($this->task == 'paypal_activate_sub') {
							if (!$model->PayPalActivateSub($this->pdata,$this->completition)) {
								$url = 'index.php?option=com_mpoll&view=mpoll&task=pay&poll=' . $this->pollid .'&payment=' . $this->payment;
								if ( $this->params->get( 'rtmpl', '' ) ) {
									$url .= '&tmpl=' . $rtmpl;
								}
								$app->enqueueMessage($model->getError(), 'error');
								$app->redirect(JRoute::_($url,false));
							}
							$url = 'index.php?option=com_mpoll&view=mpoll&task=results&poll=' . $this->pollid . '&cmpl=' . $this->payment;
							$app->redirect(JRoute::_($url,false));
						}
						if ($this->task == 'paypal_cancel') {
							$model->PayPalCancel($this->pdata,$this->completition);
							$url = 'index.php?option=com_mpoll&view=mpoll&task=pay&poll=' . $this->pollid . '&payment=' . $this->payment;
							$app->redirect(JRoute::_($url,false));
						}
						if ($this->task == 'paypal_cancel_link') {
							$model->PayPalCancel($this->pdata,$this->completition);
							$url = 'index.php?option=com_mpoll&view=mpoll&task=pay&poll=' . $this->pollid . '&payment=' . $this->payment;
							$app->redirect(JRoute::_($url,false));
						}
						if ($this->task == 'pay') {
							$subplanTrigger = explode("-",$this->pdata->poll_payment_subplan_trigger);
							$this->needsSub = $model->checkForNeededSub($this->completition,$subplanTrigger[0],$subplanTrigger[1]);
							if (str_starts_with($this->pdata->poll_payment_subplan,'P-') && $this->needsSub) {
								$doc->addScript( 'https://www.paypal.com/sdk/js?client-id='.$this->cfg->paypal_api_id.'&vault=true&intent=subscription' );
								// Sub
								$this->subinfo = $model->PayPalGetPlan($this->pdata->poll_payment_subplan);
							} else {
								$doc->addScript( 'https://www.paypal.com/sdk/js?client-id='.$this->cfg->paypal_api_id );
								// No Sub
								$this->subinfo=false;
							}
						}
					}
				}
				break;
			case "castvote": //save vote results
				if ($this->cmplid=$model->save($this->pollid)) {
					$completition = $model->getCompletition($this->cmplid);
					if ($completition->cm_status == "unpaid"){ //payment
						$url = 'index.php?option=com_mpoll&view=mpoll&task=pay&poll='.$this->pollid.'&payment=' . base64_encode('cmplid='.$this->cmplid.'&id=' . $completition->cm_pubid);
					} else { //no payment
						if ($this->pdata->poll_redirect) { // redirect to url
							$url = $this->pdata->poll_redirect_url;
						} else { // show results
							$url = 'index.php?option=com_mpoll&view=mpoll&task=results&poll=' . $this->pollid . '&cmpl=' . base64_encode('cmplid=' . $this->cmplid . '&id=' . $completition->cm_pubid);
							if ($this->params->get('rtmpl', '')) {
								$url .= '&tmpl=' . $rtmpl;
							}
						}
					}
					$app->redirect(JRoute::_($url,false));
				} else {
					$url = 'index.php?option=com_mpoll&view=mpoll&task=ballot&poll='.$this->pollid.'&cmplid='.$this->cmplid;
					$app->enqueueMessage($model->getError(), 'error');
					$app->redirect(JRoute::_($url,false));
				}
				break;
			case 'results': //Show Results
				$this->cmpl = $jinput->getVar('cmpl',null);
				if ($this->cmpl) {
					$this->cmplurl = JRoute::_( 'index.php?option=com_mpoll&view=mpoll&task=results&poll=' . $this->pollid . '&cmpl=' . $this->cmpl );
					$this->payurl  = JRoute::_( 'index.php?option=com_mpoll&view=mpoll&task=pay&poll=' . $this->pollid . '&payment=' . $this->cmpl );
					$cmpldetails   = array();
					parse_str( base64_decode( $this->cmpl ), $cmpldetails );
					$this->completition = $model->getCompletition( (int)$cmpldetails['cmplid'], $cmpldetails['id'] );

					$this->qdata = $model->getQuestions( $this->pollid, true, true );
					$this->task  = 'results';

					if ( $this->completition ) {
						$this->qdata = $model->applyAnswers( $this->qdata, $this->completition->cm_id );
					}
					$this->fcast = $model->getFirstCast( $this->pollid );
					$this->lcast = $model->getLastCast( $this->pollid );
					$this->ncast = $model->getNumCast( $this->pollid );
					$this->print = $jinput->getInt( 'print', 0 );
				} else {
					$this->task = "completed";
				}
				break;
			case 'search':
				$this->filterForm = $model->getFilterForm($this->pollid);// needs to be first to get submitted filters
				$this->filterQuestions = $model->getQuestions($this->pollid,false,false,true);
				$this->items = $model->getAllResults($this->pdata,$this->filterQuestions);
				$this->pdata->resultsMsg = $model->getResultsMessage($this->pdata,count($this->items));
				break;
			case 'ballot': //Show Questions
			default:
				// Load reCAPTCHA JS if enabled
				if ( $this->pdata->poll_recaptcha ) {
					if ($this->cfg->rc_theme == "v3") { // v3
						$doc->addScript( 'https://www.google.com/recaptcha/api.js?render=' . $this->cfg->rc_api_key );
					} else { // v2
						$doc->addScript( 'https://www.google.com/recaptcha/api.js' );
					}
				}

				// get questins
				$this->qdata=$model->getQuestions($this->pollid,true);

				break;
		}

		if (!$output) {
			parent::display( $tpl );
		} else {
			$doc = JFactory::getDocument();
			$doc->setMimeEncoding('application/json');
			echo $output;
			exit;
		}


	}

	protected function _prepareTitle()
	{
		$app     = JFactory::getApplication();
		$menus   = $app->getMenu();
		$title   = null;
		/**
		 * Because the application sets a default page title,
		 * we need to get it from the menu item itself
		 */
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}
		$title = $this->params->get('page_title', $this->pdata->poll_name);
		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		if (empty($title))
		{
			$title = $this->pdata->poll_name;
		}
		$this->document->setTitle($title);

	}

}
?>
