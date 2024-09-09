<?php

use YOOtheme\Builder;
use YOOtheme\Path;
use YOOtheme\Builder\BuilderConfig;

include_once __DIR__ . '/src/MPollSourceListener.php';
include_once __DIR__ . '/src/MPollMatchTemplate.php';
include_once __DIR__ . '/src/MPollProvider.php';
include_once __DIR__ . '/src/Type/MPollResultsType.php';
include_once __DIR__ . '/src/Type/MPollResultsQueryType.php';
include_once __DIR__ . '/src/Type/MPollSearchFormType.php';
include_once __DIR__ . '/src/Type/MPollPollType.php';
include_once __DIR__ . '/src/Type/MPollQuestionOptionType.php';
include_once __DIR__ . '/src/Type/MPollPageSearchType.php';
include_once __DIR__ . '/src/Type/MPollPageFilterFormType.php';
include_once __DIR__ . '/src/Type/MPollPagePollType.php';



return [

    'events' => [
        'source.init' => [MPollSourceListener::class => 'initSource'],
        'builder.template' => [MPollMatchTemplate::class => '@handle'],
        BuilderConfig::class => [MPollSourceListener::class => '@initCustomizer']

    ]

];
