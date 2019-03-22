<?php

jimport( 'joomla.application.component.view');


class MPollViewMPoll extends JViewLegacy
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$model = $this->getModel();
		
		$this->params = $app->getParams();
		$this->pollid = JRequest::getVar( 'poll' );
		$this->task = JRequest::getVar('task');
		$this->cmplid = JRequest::getVar( 'cmplid' );
		$this->pdata=$model->getPoll($this->pollid);
		$this->ended=false;
		
		//check if poll exists
		if (empty($this->pdata)) { 
			JError::raiseError(404, JText::_('COM_MPOLL_POLL_NOT_FOUND'));
			return false;
		}
		
		//Check Availablity
		if ((strtotime($this->pdata->poll_start) > strtotime(date("Y-m-d H:i:s"))) && $this->pdata->poll_start != '0000-00-00 00:00:00') { 
			JError::raiseError(404, JText::_('COM_MPOLL_POLL_NOT_AVAILABLE'));
			return false;
		}
		if ((strtotime($this->pdata->poll_end) < strtotime(date("Y-m-d H:i:s"))) && $this->pdata->poll_start != '0000-00-00 00:00:00') {
			$this->ended=true;
			if (!$this->pdata->poll_showended) {
				JError::raiseError(404, JText::_('COM_MPOLL_POLL_NOT_AVAILABLE'));
				return false;
			}
		}
		
		//Check for previous submission when only 1 submission allowed
		if ($this->pdata->poll_only && $user->id) {
			if ($model->getCasted($this->pollid)) $this->task='results';			
		}

		// Load reCAPTCHA Library
		if ($this->pdata->poll_recaptcha) {
			$doc = JFactory::getDocument();
			$doc->addScript('https://www.google.com/recaptcha/api.js');
		}


		switch ($this->task) {
			case "castvote": //save vote results
				if ($this->cmplid=$model->save($this->pollid)) {
					$url = 'index.php?option=com_mpoll&task=results&poll='.$this->pollid.'&cmplid='.$this->cmplid;
					if ($this->params->get('rtmpl','')) $url .= '&tmpl='.$rtmpl;
					$app->redirect(JRoute::_($url,false));
				} else {
					$url = 'index.php?option=com_mpoll&task=ballot&poll='.$this->pollid.'&cmplid='.$this->cmplid;
					$app->redirect(JRoute::_($url,false),$model->getError(),"error");
				}
				break;
			case 'results': //Show Results
				$this->qdata=$model->getQuestions($this->pollid,true,true);
				$this->task='results';
					
				if ($this->cmplid) $this->qdata = $model->applyAnswers($this->qdata,$this->cmplid);
				$this->fcast = $model->getFirstCast($this->pollid);
				$this->lcast = $model->getLastCast($this->pollid);
				$this->ncast = $model->getNumCast($this->pollid);
				$this->print = JRequest::getInt( 'print',0 );
				parent::display($tpl);
				break;
			case 'ballot': //Show Questions
			default:
				$this->qdata=$model->getQuestions($this->pollid,true);
				parent::display($tpl);
				break;
		}

	}
}
?>
