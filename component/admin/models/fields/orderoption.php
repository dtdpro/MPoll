<?php

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldOrderOption extends JFormField
{

	protected $type = 'OrderOption';

	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// Get some field values from the form.
		$optionId	= (int) $this->form->getValue('opt_id');
		$qId	= (int) $this->form->getValue('opt_qid');

		// Build the query for the ordering list.
		$query = 'SELECT ordering AS value, opt_txt AS text' .
				' FROM #__mpoll_questions_opts' .
				' WHERE opt_qid = ' . (int) $qId .
				' ORDER BY ordering';

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true') {
			$html[] = JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $optionId ? 0 : 1);
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
		}
		// Create a regular list.
		else {
			$html[] = JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $optionId ? 0 : 1);
		}

		return implode($html);
	}
}
