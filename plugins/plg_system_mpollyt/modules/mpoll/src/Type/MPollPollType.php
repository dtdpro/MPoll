<?php

class MPollPollType
{
    public static function config()
    {
        $config = [

            'fields' => [
                'resultsMsg' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Poll Results Mesasge'
                    ]
                ],
                'poll_name' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Poll Name'
                    ]
                ],
            ],

            'metadata' => [
                'type' => true,
                'label' => 'MPoll Poll'
            ]
        ];



        return $config;
    }






}
