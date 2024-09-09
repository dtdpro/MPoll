<?php


use Joomla\CMS\Document\Document;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;

class MPollMatchTemplate
{
    public Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function handle($view, $tpl): ?array
    {
        if ($tpl) {
            return null;
        }

        $task = $view->get('task');
        $context = $view->get('context');

        if ($context === 'com_mpoll.mpoll' && $task == 'search') {
            $pollData = $view->pdata;

            return [
                'type' => $context,
                'query' => [
                    'poll' => $pollData->poll_id
                ],
                'params' => [
                    'pollData' => $pollData,
                    'items' => $view->items,
                    'filterForm' => $view->filterForm,
                    'filterQuestions' => $view->filterQuestions
                ],
            ];
        }




        return null;
    }
}
