<?php

use JFactory;

class MPollPagePollType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [
            'fields' => [
                'mpollpoll' => [
                    'type' => 'MPollPollType',
                    'metadata' => [
                        'label' => 'MPoll Poll',
                        'view' => ['com_mpoll.mpoll'],
                        'group' => 'MPoll',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolve',
                    ],
                ]
            ],
        ];
    }

    public static function resolve($root, array $args)
    {
        return $root['pollData'];;
    }
}
