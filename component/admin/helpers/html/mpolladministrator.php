<?php
defined('_JEXEC') or die;

abstract class JHtmlMPollAdministrator
{	
	public static function questions($i, $canEdit = true)
	{
		JHtml::_('bootstrap.tooltip');
	
		if ($canEdit)
		{
			$html	= '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'mpolls.questions\')" class="btn btn-micro hasTooltip' . '" title="Questions"><i class="icon-question"></i></a>';
		}
		else
		{
			$html = '';
		}
	
		return $html;
	}
}

