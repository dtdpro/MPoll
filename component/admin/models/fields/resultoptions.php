<?php

defined('JPATH_PLATFORM') or die;


class JFormFieldResultOptions extends JFormField
{
	protected $type = 'ResultOptions';

	protected function getInput()
	{
		$id	= (int) JRequest::getVar( 'poll_id' );
		if (!$id) return '<input type="hidden" name="' . $name . '" value="0" />' . '<span class="readonly">Available Once Poll is Saved</span>';

		$result_options = array();
		//$result_options[] = JHtml::_('select.option', "ALL","Any Option");

		$db = JFactory::getDBO();
		$query=$db->getQuery(true);
		$query->select('*');
		$query->from('#__mpoll_questions');
		$query->where('q_poll = ' . $id);
		$query->where('q_type IN ("multi","dropdown","mlist")');
		$query->order('ordering');
		$db->setQuery($query);

		$questions = $db->loadObjectList();

		foreach ($questions as $q) {
			$questions_options = array();
			//$result_options[] = JHtml::_('select.option', "0",$q->q_name,"value","text",true);
			$opt_val = $q->q_id."_";

			$optquery=$db->getQuery(true);
			$optquery->select('*');
			$optquery->from('#__mpoll_questions_opts');
			$optquery->where('opt_qid = ' . (int) $q->q_id);
			$optquery->order('ordering');
			$db->setQuery($optquery);
			$options = $db->loadObjectList();

			foreach ($options as $o) {
				$questions_options[] = JHtml::_('select.option', $opt_val.$o->opt_id,$o->opt_txt);
			}
			$result_options[$q->q_name] = $questions_options;
		}


		//$html[] = '<select name="'.$this->name.'" class="inputbox" '.$attr.'>';
		$html[] = JHtml::_('select.groupedlist',$result_options,$this->name, array(
			'list.attr' => $attr, 'id' => $this->id, 'list.select' => $this->value, 'group.items' => null, 'option.key.toHtml' => false,
			'option.text.toHtml' => false,
		));
		//$html[] = '</select>';
		return implode($html);;
	}
	
}
