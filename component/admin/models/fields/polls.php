<?php
/**
 * @version		$Id: ordering.php 21097 2011-04-07 15:38:03Z dextercowley $
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 *
 * @package		Joomla.Administrator
 * @subpackage	com_mpoll
 * @since		1.6
 */
class JFormFieldPolls extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Polls';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';
		$db = JFactory::getDBO();
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';


		// Build the query for the ordering list.
		$query = 'SELECT poll_id AS value, poll_name AS text' .
				' FROM #__mpoll_polls' .
				' ORDER BY poll_name';
		$db->setQuery($query);
		$html[] = JHtml::_('select.genericlist',$db->loadObjectList(),$this->name,$attr, "value","text",$this->value);
		

		return implode($html);
	}
}
