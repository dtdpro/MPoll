<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );
		
class MPollViewAnsQuest extends JView
{
	function display($tpl = null)
	{
		global $mainframe,$option;
		$qid = JRequest::getVar('opt_qid');
		$cid = JRequest::getVar('q_poll');
		JToolBarHelper::title(   JText::_( 'MPoll Poll Question Results by Question' ), 'generic.png' );
		$tbar =& JToolBar::getInstance('toolbar');
		$tbar->appendButton('Link','archive','Export CSV','index.php?option=com_movte&controller=ansquest&task=csvme&opt_qid='.$qid.'&format=raw');
		JToolBarHelper::back('Questions','index.php?option=com_mpoll&view=question&q_poll='.$cid);
		// Get data from the model
		$model = $this->getModel('ansquest');
		$data = $model->getQInfo($qid);
		$qtype = $data->q_type;
		$items = $model->getResponses($qid,$qtype);
		
		$this->assignRef('data',		$data);
		$this->assignRef('items',		$items);
		parent::display($tpl);
	}
}
