<?php

defined('JPATH_PLATFORM') or die;


class JFormFieldPollEmailFields extends JFormField
{
	protected $type = 'PollEmailFields';

	protected function getInput()
	{
		$id	= (int) $this->form->getValue('poll_id');
		if (!$id) return '<input type="hidden" name="' . $name . '" value="0" />' . '<span class="readonly">Available Once Poll is Saved</span>';

		$html = array();
		$attr = '';
		$db = JFactory::getDBO();
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		if (!$id) return $this->getTextField();

		// Build the query for the ordering list.
		$html[] = '<select name="'.$this->name.'" class="inputbox" '.$attr.'>';
		$html[] = '<option value="">None</option>';
		$query = 'SELECT q_id AS value, q_name AS text' .
		         ' FROM #__mpoll_questions' .
		         ' WHERE q_type = "email" && q_poll = ' . $id .
		         ' ORDER BY ordering';
		$db->setQuery($query);
		$html[] = JHtml::_('select.options',$db->loadObjectList(),"value","text",$this->value);
		$html[] = '</select>';


		return implode($html);
	}

	
}
