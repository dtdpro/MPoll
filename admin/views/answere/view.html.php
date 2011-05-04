<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MPollViewAnswerE extends JView
{
	function display($tpl = null)
	{
		//get the hello
		$questionid = JRequest::getVar('opt_qid');
		$pollid = JRequest::getVar('q_poll');
		$answer		=& $this->get('Data');
		$isNew		= ($answer->opt_id < 1);

		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'MPoll Option' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}

		$this->assignRef('answer',		$answer);
		$this->assignRef('questionid',$questionid);
		$this->assignRef('pollid',$pollid);
		$editor =& JFactory::getEditor();
		$this->assignref('editor',$editor);
		parent::display($tpl);
	}
}
