<?php

defined('JPATH_PLATFORM') or die;


class JFormFieldTmplEmailFields extends JFormField
{
	protected $type = 'TmplEmailFields';

	protected function getInput()
	{
		$id	= (int) $this->form->getValue('tmpl_poll');
		if (!$id) return '<input type="hidden" name="' . $this->name . '" value="0" />' . '<span class="readonly">Available Once Poll is Saved</span>';

		$html = array();
		$attr = '';
		$db = JFactory::getDBO();
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// Build the query for the ordering list.
		$html[] = '<select name="'.$this->name.'" class="inputbox form-select" '.$attr.'>';
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
