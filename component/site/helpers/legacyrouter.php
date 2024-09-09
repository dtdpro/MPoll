<?php

defined('_JEXEC') or die;

class MPollRouterRulesLegacy implements JComponentRouterRulesInterface
{
	public function __construct($router)
	{
		$this->router = $router;
	}

	public function preprocess(&$query)
	{
	}

	public function build(&$query, &$segments)
	{
		$params = JComponentHelper::getParams('com_mpoll');

		if (empty($query['Itemid']))
		{
			$menuItem = $this->router->menu->getActive();
		}
		else
		{
			$menuItem = $this->router->menu->getItem($query['Itemid']);
		}

		$mView = empty($menuItem->query['view']) ? null : $menuItem->query['view'];
		$mId = empty($menuItem->query['poll']) ? null : $menuItem->query['poll'];

		if (isset($query['view']))
		{
			$view = $query['view'];

			if (empty($query['Itemid']) || empty($menuItem) || $menuItem->component != 'com_mpoll')
			{
				$segments[] = $query['view'];
			}

			unset($query['view']);
		}

		if (isset($query['task']) && $query['task'] == 'ballot') {
			unset($query['task'],$segments['task']);
		}

        if (isset($query['task']) && $query['task'] == 'search') {
            unset($query['task'],$segments['task']);
        }

		// Are we dealing with a poll that is attached to a menu item?
		if (isset($view) && ($mView == $view) && isset($query['poll']) && ($mId == (int) $query['poll']))
		{
			unset($query['view'], $query['poll']);

			return;
		}

		if (isset($view) && ($view == 'mpoll'))
		{
			if ($mId != (int) $query['poll'] || $mView != $view)
			{
				if ($view == 'mpoll')
				{

					$id = $query['poll'];

					$segments[] = $id;
				}
			}

			unset($query['poll']);
		}
	}

	public function parse(&$segments, &$vars)
	{

	}
}
