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
	
	public static function options($i, $type, $canEdit = true)
	{
		JHtml::_('bootstrap.tooltip');
	
		if ($canEdit && ($type=='mlist' || $type=='multi' || $type=='mcbox' || $type=='dropdown'))
		{
			$html	= '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'questions.options\')" class="btn btn-micro hasTooltip' . '" title="Options"><i class="icon-list"></i></a>';
		}
		else
		{
			$html = '<span class="btn btn-micro hasTooltip disabled"><i class="icon-list"></i></span>';
		}
	
		return $html;
	}
}

