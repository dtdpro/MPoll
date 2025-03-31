<?php
defined('_JEXEC') or die;

abstract class JHtmlMPollAdministrator
{
    public static function questions($i, $type, $canEdit = true)
    {
        JHtml::_('bootstrap.tooltip');

        if ($canEdit && $type == 'question') {
            $html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'mpolls.questions\')" title="Questios" class="btn btn-primary btn-sm"><i class="fa fa-sm fa-question"></i> Questions</a>';
        } else {
            $html = '<span class="disabled btn btn-sm btn-secondary"><i class="fa fa-sm fa-question"></i> Questions</span>';
        }

        return $html;
    }

    public static function options($i, $type, $canEdit = true)
    {
        JHtml::_('bootstrap.tooltip');

        if ($canEdit && ($type=='mlist' || $type=='multi' || $type=='mcbox' || $type=='dropdown')) {
            $html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'questions.options\')" title="Options" class="btn btn-primary btn-sm"><i class="fa fa-sm fa-list"></i> Options</a>';
        } else {
            $html = '<span class="disabled btn btn-sm btn-secondary"><i class="fa fa-sm fa-list"></i> Options</span>';
        }

        return $html;
    }

    public static function results($i, $canEdit = true, $elgibleItems = 1)
    {
        JHtml::_('bootstrap.tooltip');

        if ($canEdit && $elgibleItems) {
            $html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'mpolls.pollresults\')" title="Records" class="btn btn-primary btn-sm"><i class="fa fa-sm fa-database"></i> Results</a>';
        } else {
            $html = '<span class="disabled btn btn-sm btn-secondary"><i class="fa fa-sm fa-database"></i> Results</span>';
        }

        return $html;
    }

    public static function tally($i, $canEdit = true, $elgibleItems = 1)
    {
        JHtml::_('bootstrap.tooltip');

        if ($canEdit && $elgibleItems) {
            $html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'mpolls.tally\')" title="Records" class="btn btn-primary btn-sm"><i class="fa fa-sm fa-chart-bar"></i> Tally</a>';
        } else {
            $html = '<span class="disabled btn btn-sm btn-secondary"><i class="fa fa-sm fa-chart-bar"></i> Tally</span>';
        }

        return $html;
    }

    public static function emailtemplates($i, $canEdit = true, $elgibleItems = 1)
    {
        JHtml::_('bootstrap.tooltip');

        if ($canEdit && $elgibleItems) {
            $html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'mpolls.emailtemplates\')" title="Records" class="btn btn-primary btn-sm"><i class="fa fa-sm fa-envelope"></i> Email Templates</a>';
        } else {
            $html = '<span class="disabled btn btn-sm btn-secondary"><i class="fa fa-sm fa-envelope"></i> Email Templates</span>';
        }

        return $html;
    }
}

