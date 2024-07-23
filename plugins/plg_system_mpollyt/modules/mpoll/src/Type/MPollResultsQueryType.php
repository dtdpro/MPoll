<?php

use YooTheme\Database;

class MPollResultsQueryType
{

    public static function config()
    {
        $polls = self::getPolls();
        return [

            'fields' => [

                'mpollresultsquerytype' => [

                    'type' => [
                        'listOf' => 'MPollResultsType',
                    ],

                    'args' => [
                        'pollid' => [
                            'type' => 'String',
                        ],
                        'orderby1' => [
                            'type' => 'String',
                        ],
                        'limit' => [
                            'type' => 'String',
                        ]
                    ],

                    'metadata' => [

                        'label' => 'MPoll Results',
                        'group' => 'MPoll',
                        'fields' => [
                            'pollid' => [
                                'label' => 'Poll/Form ID',
                                'type' => 'select',
                                'default' => '',
                                "description"=> "Poll ID, Required.",
                                'options' => $polls
                            ],
                            'limit' => [
                                'label' => 'Limit',
                                'type' => 'text',
                                'default' => '5',
                            ]
                        ],

                    ],

                    'extensions' => [
                        'call' => __CLASS__ . '::resolve',
                    ],

                ],

            ]

        ];
    }

    public static function resolve($item, $args, $context, $info)
    {
        return MPollProvider::getResults($args['pollid'],$args['limit']);
    }

    public static function getPolls()
    {
        return array_map(function($poll) {
            return ['value'=>strval($poll->poll_id),'text'=>$poll->poll_name.' ('.$poll->poll_id.')'];
        }, MPollProvider::pollList());
    }

}
