<?php
use Joomla\CMS\Component\Router\Rules\RulesInterface;


defined('_JEXEC') or die;

class MPollRouterRulesLegacy implements RulesInterface
{
	public function __construct($router)
	{
		$this->router = $router;
	}

	public function preprocess(&$query)
	{
        $items = [];
        $task = 'ballot';
        $pollId = 0;
        $matchedTaskAndPollId = 0;
        $matchedPollId = 0;


        $app = JFactory::getApplication();
        $menu	= $this->router->menu;
        $items	= $menu->getItems('component', 'com_mpoll');

        if (isset($query['task'])) $task = $query['task'];
        if (isset($query['poll'])) $pollId = $query['poll'];

        foreach ($items as $mi) {
            if ($mi->query['task'] == $task && $mi->query['poll'] == $pollId) {
                $matchedTaskAndPollId = $mi->id;
            }

            if ($mi->query['poll'] == $pollId && $matchedTaskAndPollId == 0) {
                $matchedPollId = $mi->id;
            }
        }

        if ($matchedTaskAndPollId != 0) $query['Itemid'] = $matchedTaskAndPollId;
        else if ($matchedPollId != 0) $query['Itemid'] = $matchedPollId;
    }

	public function build(&$query, &$segments)
	{
        $items = [];
        $task = 'ballot';
        $pollId = 0;
        $matchedTaskAndPollId = 0;
        $matchedPollId = 0;

        $app = JFactory::getApplication();
        $menu	= $this->router->menu;
        $items	= $menu->getItems('component', 'com_mpoll');

        if (isset($query['task'])) $task = $query['task'];
        if (isset($query['poll'])) $pollId = $query['poll'];

        foreach ($items as $mi) {
            if ($mi->query['task'] == $task && $mi->query['poll'] == $pollId) {
                $matchedTaskAndPollId = true;
            }

            if ($mi->query['poll'] == $pollId  && !$matchedTaskAndPollId) {
                $matchedPollId = true;
            }
        }

        if ($matchedTaskAndPollId ) {
            unset($query['task']);
            unset($query['poll']);
            unset($query['view']);
        }
        else if ($matchedPollId) {
            unset($query['poll']);
            unset($query['view']);
        }
	}

	public function parse(&$segments, &$vars)
	{

	}
}
