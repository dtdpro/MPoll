<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MPollViewAnswer extends JView
{
	function display($tpl = null)
	{
		$pollid = JRequest::getVar('q_poll');
		$questionid = JRequest::getVar('opt_qid');
		JToolBarHelper::title(   JText::_( 'MPoll Option Manager' ), 'generic.png' );
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::back('Questions','index.php?option=com_mpoll&view=question&q_poll='.$pollid);

		// Get data from the model
		$items		= & $this->get( 'Data');
		$pagination = & $this->get( 'Pagination' );

		$model=$this->getModel();
		$pollinfo = $model->getPollInfo($pollid);
		$qinfo = $model->getQInfo($questionid);
		
		$this->assignRef('items',		$items);
		$this->assignRef('questionid',$questionid);
		$this->assignRef('pollid',$pollid);
	    $this->assignRef('pagination',	$pagination);
		$this->assignRef('pollinfo',$pollinfo);
		$this->assignRef('qinfo',$qinfo);
		
		parent::display($tpl);
	}
}
