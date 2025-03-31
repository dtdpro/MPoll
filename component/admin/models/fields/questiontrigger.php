<?php

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldQuestionTrigger extends JFormField
{
	protected $type = 'QuestionTrigger';

	protected function getInput()
	{
		// Initialize variables.
		$db = JFactory::getDBO();
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		//$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';


		// Get some field values from the form.
		$pollId	= (int) $this->form->getValue('poll_id');

		// Build the query for the ordering list.
		$query=$db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_questions');
		$query->where('q_poll = ' . (int) $pollId);
		$query->where('q_type IN ("cbox","multi","dropdown")');
		$query->order('ordering');
		$db->setQuery($query);
        $questions = $db->loadObjectList();
        $options = [];
        foreach ($questions as $question) {
            if ($question->q_type == 'cbox') {
                $option = [];
                $option['value'] = $question->q_id.'-'.'1';
                $option['text'] = $question->q_name.' - Checked';
                $options[] = $option;
            } else {
                $query=$db->getQuery(true);
                $query->select('*');
                $query->from('#__mpoll_questions_opts');
                $query->where('opt_qid = ' . (int) $question->q_id);
                $query->order('ordering');
                $db->setQuery($query);
                $qoptions = $db->loadObjectList();
                foreach ($qoptions as $qoption) {
                    $option = [];
                    $option['value'] = $question->q_id.'-'.$qoption->opt_id;
                    $option['text'] = $question->q_name.' - '.$qoption->opt_txt;
                    $options[] = $option;
                }
            }
        }
		$html[] = '<select name="'.$this->name.'" class="form-select" '.$attr.'>';
        $html[] = JHtml::_('select.options',[['value'=>"none",'text'=>"None"]],"value","text",$this->value);
		$html[] = JHtml::_('select.options',$options,"value","text",$this->value);
		$html[] = '</select>';

		return implode($html);
	}
}