<?php

use YOOtheme\Builder;
use YOOtheme\Path;
use YOOtheme\Builder\BuilderConfig;

include_once __DIR__ . '/src/MPollSourceListener.php';
include_once __DIR__ . '/src/MPollProvider.php';
include_once __DIR__ . '/src/Type/MPollResultsType.php';
include_once __DIR__ . '/src/Type/MPollResultsQueryType.php';


return [

    'events' => [
        'source.init' => [
            MPollSourceListener::class => 'initSource',
        ],
        //'builder.template' => [MatchTemplate::class => '@handle'],
        /*'customizer.init' => [
	        SourceListener::class => ['initCustomizer',10],
        ],*/
        BuilderConfig::class => [MPollSourceListener::class => '@initCustomizer']

    ],

];
