<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class MPollViewQuestion extends JView
{
	function display($tpl = null)
	{
		$pollid = JRequest::getVar('q_poll');
		JToolBarHelper::title(   JText::_( 'MPoll Question Manager' ), 'generic.png' );
		JToolBarHelper::publishList('reqpublish','Require');
		JToolBarHelper::unpublishList('requnpublish','UnRequire');
		JToolBarHelper::custom( 'move', 'move.png', 'move_f2.png', 'Move', true );
		JToolBarHelper::custom( 'copy', 'copy.png', 'copy.png', 'Copy', true, true );
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::back('Polls','index.php?option=com_mpoll&view=mpoll');

		// Get data from the model
		$items		= & $this->get( 'Data');
		$pagination = & $this->get( 'Pagination' );

		$model=$this->getModel();
		$pollinfo = $model->getPollInfo($pollid);
		
		$this->assignRef('items',		$items);
		$this->assignRef('pollid',$pollid);
		$this->assignRef('pollinfo',$pollinfo);
	    $this->assignRef('pagination',	$pagination);
		
		parent::display($tpl);
	}
}
