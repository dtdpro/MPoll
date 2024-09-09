<?php

use JFactory;

class MPollPageFilterFormType
{
    /**
     * @return array
     */
    public static function config()
    {
        return [
            'fields' => [
                'mpollsearchableform' => [
                    'type' => 'MPollSearchFormType',
                    'metadata' => [
                        'label' => 'MPoll Searchable Results Form',
                        'view' => ['com_mpoll.mpoll'],
                        'group' => 'MPoll'
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
        $item['filterForm'] = $root['filterForm'];
        $item['filterQuestions'] = $root['filterQuestions'];
        return $item;
    }
}
