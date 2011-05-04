<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MPollViewQuestionE extends JView
{
	function display($tpl = null)
	{
		if (JRequest::getVar( 'layout') == 'move') $this->moveForm();
		else {
		$pollid = JRequest::getVar('q_poll');
		$question		=& $this->get('Data');
		$isNew		= ($question->q_id < 1);
		
		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'MPoll Question' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}

		$this->assignRef('question',		$question);
		$this->assignRef('pollid',$pollid);
		$editor =& JFactory::getEditor();
		$this->assignref('editor',$editor);
		parent::display($tpl);
		}
	}
	
	function moveForm($tpl=null)
	{
		global $mainframe;

		JToolBarHelper::title( JText::_( 'Questions' ) . ': <small><small>[ '. JText::_( 'Move' ) .' ]</small></small>' );
		JToolBarHelper::custom( 'doMove', 'move.png', 'move_f2.png', 'Move', false );
		JToolBarHelper::cancel();

		$pollid = JRequest::getVar('q_poll');
		$document = & JFactory::getDocument();
		$document->setTitle(JText::_('Move Questions'));

		// Build the menutypes select list
		$model=$this->getModel();
		$polls 	= $model->getPollList();
		foreach ( $polls as $poll ) {
			$pollslist[] = JHTML::_('select.option',  $poll->poll_id, $poll->poll_name );
		}
		$polllist = JHTML::_('select.genericlist',   $pollslist, 'newpoll', 'class="inputbox" size="10"', 'value', 'text', null );

		$qus = &$this->get('QuestionsFromRequest');

		$this->assignRef('polllist', $polllist);
		$this->assignRef('qus', $qus);
		$this->assignRef('pollid', $pollid);

		parent::display($tpl);
	}
}
