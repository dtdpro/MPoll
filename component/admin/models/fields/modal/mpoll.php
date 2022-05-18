<?php

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

class JFormFieldModal_MPoll extends JFormField
{
	protected $type = 'Modal_MPoll';

	protected function getInput()
	{
		$allowClear     = ((string) $this->element['clear'] != 'false');
		$allowSelect    = ((string) $this->element['select'] != 'false');

		// The active contact id field.
		$value = (int) $this->value > 0 ? (int) $this->value : '';

		// Create the modal id.
		$modalId = 'MPoll_' . $this->id;

		// Add the modal field script to the document head.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));

		// Script to proxy the select modal function to the modal-fields.js file.
		if ($allowSelect)
		{
			static $scriptSelect = null;

			if (is_null($scriptSelect))
			{
				$scriptSelect = array();
			}

			if (!isset($scriptSelect[$this->id]))
			{
				JFactory::getDocument()->addScriptDeclaration("
				function jSelectMPoll_" . $this->id . "(id, title, object) {
					window.processModalSelect('MPoll', '" . $this->id . "', id, title, '', object);
				}
				");

				$scriptSelect[$this->id] = true;
			}
		}

		// Setup variables for display.
		$linkMPoll = 'index.php?option=com_mpoll&amp;view=mpolls&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
		$modalTitle   = JText::_('COM_MPOLL_SELECT_A_POLL');

		$urlSelect = $linkMPoll . '&amp;function=jSelectMPoll_' . $this->id;

		if ($value)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
			            ->select($db->quoteName('poll_name'))
			            ->from($db->quoteName('#__mpool_polls'))
			            ->where($db->quoteName('poll_id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}

		$title = empty($title) ? JText::_('COM_MPOLL_SELECT_A_POLL') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current contact display field.
		$html  = '<span class="input-append">';
		$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// Select contact button
		if ($allowSelect)
		{
			$html .= '<button'
			         . ' type="button"'
			         . ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
			         . ' id="' . $this->id . '_select"'
			         . ' data-toggle="modal"'
			         . ' data-target="#ModalSelect' . $modalId . '"'
			         . ' title="Select an MPoll">'
			         . '<span class="icon-file" aria-hidden="true"></span> ' . JText::_('JSELECT')
			         . '</button>';
		}

		// Clear contact button
		if ($allowClear)
		{
			$html .= '<button'
			         . ' type="button"'
			         . ' class="btn' . ($value ? '' : ' hidden') . '"'
			         . ' id="' . $this->id . '_clear"'
			         . ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
			         . '<span class="icon-remove" aria-hidden="true"></span>' . JText::_('JCLEAR')
			         . '</button>';
		}

		$html .= '</span>';

		// Select contact modal
		if ($allowSelect)
		{
			$html .= JHtml::_(
				'bootstrap.renderModal',
				'ModalSelect' . $modalId,
				array(
					'title'       => $modalTitle,
					'url'         => $urlSelect,
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => '70',
					'modalWidth'  => '80',
					'footer'      => '<button type="button" class="btn" data-dismiss="modal">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
				)
			);
		}

		// Note: class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		$html .= '<input type="hidden" id="' . $this->id . '_id"' . $class . ' data-required="' . (int) $this->required . '" name="' . $this->name
		         . '" data-text="' . htmlspecialchars(JText::_('COM_MPOLL_SELECT_A_POLL', true), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '" />';

		return $html;
	}
}