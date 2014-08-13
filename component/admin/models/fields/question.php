<?php

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldQuestion extends JFormField
{
	protected $type = 'Question';

	protected function getInput()
	{
		// Initialize variables.
		$db = JFactory::getDBO();
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		$qtype = $this->element['qtype'] ? $this->element['qtype'] : '';
		
		// Get some field values from the form.
		$questionId	= (int) $this->form->getValue('q_id');
		$pollId	= (int) $this->form->getValue('q_poll');

		// Build the query for the ordering list.
		$query=$db->getQuery(true);
		$query->select('q_id AS value');
		$query->select('q_name AS text');
		$query->from('#__mpoll_questions');
		$query->where('q_poll = ' . (int) $pollId);
		if ($qtype) $query->where('q_type = "'.$qtype.'"');
		$query->order('ordering');
		$db->setQuery($query);
		$html[] = '<select name="'.$this->name.'" class="inputbox" '.$attr.'>';
		$html[] = JHtml::_('select.options',$db->loadObjectList(),"value","text",$this->value);
		$html[] = '</select>';

		return implode($html);
	}
}
