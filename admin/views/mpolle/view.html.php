<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MPollViewMPolle extends JView
{
	function display($tpl = null)
	{
		//get the hello
		$polle		=& $this->get('Data');
		$model =& $this->getModel();
		$isNew		= ($polle->poll_id < 1);

		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'Poll' ).': <small><small>[ ' . $text.' ]</small></small>' );
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}

		$this->assignRef('polle',		$polle);
		$editor =& JFactory::getEditor();
		$this->assignref('editor',$editor);
		parent::display($tpl);
	}
}
