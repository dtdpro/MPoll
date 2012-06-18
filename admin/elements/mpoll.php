<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementMPoll extends JElement
{
	var	$_name = 'MPoll';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT a.poll_id, a.poll_name'
		. ' FROM #__mpoll_polls AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.poll_name'
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList();

		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select Poll').' -', 'id', 'title'));

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'poll_id', 'poll_name', $value, $control_name.$name );
	}
}
