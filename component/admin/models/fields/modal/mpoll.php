<?php

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

class JFormFieldModal_MPoll extends JFormField
{
	protected $type = 'Modal_MPoll';

	protected function getInput()
	{
		// Load the javascript
		JHtml::_('behavior.modal', 'a.modal');

		// Build the script.
		$script = array();
		$script[] = '	function jSelectMPoll_'.$this->id.'(id, name, object) {';
		$script[] = '		document.id("'.$this->id.'_id").value = id;';
		$script[] = '		document.id("'.$this->id.'_name").value = name;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Get the title of the linked chart
		$db = JFactory::getDBO();
		$db->setQuery(
				'SELECT poll_name' .
				' FROM #__mpoll_polls' .
				' WHERE poll_id = '.(int) $this->value
		);
		$title = $db->loadResult();

		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
		}

		if (empty($title)) {
			$title = JText::_('COM_MPOLL_SELECT_A_POLL');
		}
		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		$link = 'index.php?option=com_mpoll&amp;view=mpolls&amp;layout=modal&amp;tmpl=component&amp;function=jSelectMPoll_'.$this->id;

		$html	= array();

		$html[] = '<span class="input-append">';
		$html[] = '<input type="text" class="input-medium" id="'.$this->id.'_name" value="'.$title.'" disabled="disabled" size="35" />';
		$html[] = '<a class="modal btn" title="'.JText::_('COM_MPOLL_CHANGE_POLL_BUTTON').'" href="'.$link.'&amp;'.JSession::getFormToken().'=1" rel="{handler: \'iframe\', size: {x: 900, y: 450}}"><i class="icon-file"></i> '.JText::_('JSELECT').'</a>';
		$html[] = '</span>';

		// The active newsfeed id field.
		if (0 == (int)$this->value) {
			$value = '';
		} else {
			$value = (int)$this->value;
		}

		// class='required' for client side validation
		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

		return implode("\n", $html);
	}
}