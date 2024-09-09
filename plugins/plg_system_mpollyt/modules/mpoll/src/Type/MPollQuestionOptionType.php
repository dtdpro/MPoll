<?php

class MPollQuestionOptionType
{
    public static function config()
    {
        $config = [

            'fields' => [

                'opt_txt' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Option Text'
                    ],
                ],

            ],

            'metadata' => [
                'type' => true,
                'label' => 'MPoll Option'
            ]

        ];

        return $config;
    }
}
