<?php

use JFactory;

class MPollPageSearchType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [
            'fields' => [
                'mpollsearchable' => [
                    'type' => [
                        'listOf' => 'MPollResultsType',
                    ],
                    'args' => [
                        'offset' => [
                            'type' => 'Int',
                        ],
                        'limit' => [
                            'type' => 'Int',
                        ],
                    ],
                    'metadata' => [
                        'label' => 'MPoll Searchable Results',
                        'view' => ['com_mpoll.mpoll'],
                        'group' => 'MPoll',
                    ],
                    'extensions' => [
                        'call' => __CLASS__ . '::resolve',
                    ],
                ],
            ],
        ];
    }

    public static function resolve($root, array $args)
    {
        $items = $root['items'];
        return $items;

    }
}
