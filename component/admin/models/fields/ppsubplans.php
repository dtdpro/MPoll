<?php

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldPPSubPlans extends JFormField
{
	protected $type = 'PPSubPlans';

	protected function getInput()
	{
        require JPATH_ROOT."/components/com_mpoll/vendor/autoload.php";
        require_once(JPATH_ROOT.'/components/com_mpoll/lib/paypal.php');

		// Initialize variables.
		$cfg = MPollHelper::getMPollConfig();
        $hasPayPal = true;

		$html = array();

        if ($cfg->paypal_api_id) {
            if (!$payPayService = new PayPalService($cfg->paypal_api_id, $cfg->paypal_api_secret, $cfg->paypal_mode)) {
                $hasPayPal = false;
            }

            $plans = $payPayService->listPlans();

            $attr = '';

            // Initialize some field attributes.
            //$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
            $attr .= ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
            $attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';

            // Initialize JavaScript field attributes.
            $attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

            // Get some field values from the form.
            $pollId = (int)$this->form->getValue('poll_id');

            // Build the query for the ordering list.
            $subPlans = [];
            foreach ($plans as $plan) {
                $subPlan = [];
                $subPlan['value'] = $plan['id'];
                $subPlan['text'] = $plan['name'];
                $subPlans[] = $subPlan;
            }

            $html[] = '<select name="' . $this->name . '" class="form-select" ' . $attr . '>';
            $html[] = JHtml::_('select.options', [['value' => "0", 'text' => "None"]], "value", "text", $this->value);
            if (!$hasPayPal) {
                $html[] = JHtml::_('select.options', [['value' => "0", 'text' => "PayPal Not Setup"]], "value", "text", $this->value);
            } else if (count($subPlans)) {
                $html[] = JHtml::_('select.options', $subPlans, "value", "text", $this->value);
            } else {
                $html[] = JHtml::_('select.options', [['value' => "0", 'text' => "No Plans Setup in PayPal"]], "value", "text", $this->value);
            }
            $html[] = '</select>';
        } else {
            $html[] = "PayPal is not enabled";
            $html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
        }
		return implode($html);
	}
}