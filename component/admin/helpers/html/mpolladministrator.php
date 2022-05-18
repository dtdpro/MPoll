<?php
defined('_JEXEC') or die;

abstract class JHtmlMPollAdministrator
{	
	public static function questions($i, $canEdit = true)
	{
		if ($canEdit)
		{
			if (JVersion::MAJOR_VERSION == 3) $html	= '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'mpolls.questions\')" class="btn btn-micro hasTooltip' . '" title="Questions"><i class="icon-question"></i></a>';
			else  $html	= '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'mpolls.questions\')" title="Questions">Questions</a>';
		}
		else
		{
			$html = '';
		}
	
		return $html;
	}
	
	public static function options($i, $type, $canEdit = true)
	{
		if ($canEdit && ($type=='mlist' || $type=='multi' || $type=='mcbox' || $type=='dropdown'))
		{
			if (JVersion::MAJOR_VERSION == 3) $html	= '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'questions.options\')" class="btn btn-micro hasTooltip' . '" title="Options"><i class="icon-list-2"></i></a>';
			else $html	= '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'questions.options\')" title="Options">Options</a>';
		}
		else
		{
			$html = '<span class="btn btn-micro hasTooltip disabled"><i class="icon-list-2"></i></span>';
		}
	
		return $html;
	}

	public static function results($i, $canEdit = true)
	{
		if (JVersion::MAJOR_VERSION == 3) $html	= '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'mpolls.pollresults\')"" title="Results">Results</a>';
		else $html	= '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'mpolls.pollresults\')" title="Results">Results</a>';

		return $html;
	}

	public static function tally($i, $canEdit = true)
	{
		if (JVersion::MAJOR_VERSION == 3) $html	= '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'mpolls.tally\')"" title="Tally">Tally</a>';
		else $html	= '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'mpolls.tally\')" title="Tally">Tally</a>';

		return $html;
	}
}

