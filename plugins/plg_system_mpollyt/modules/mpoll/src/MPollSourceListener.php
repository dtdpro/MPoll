<?php

use YOOtheme\Builder\Source;
use YOOtheme\Builder\BuilderConfig;

class MPollSourceListener
{
    /**
     * @param Source $source
     */
    public static function initSource($source)
    {
	    $source->objectType('MPollResultsType', MPollResultsType::config());
        $source->objectType('MPollQuestionOptionType', MPollQuestionOptionType::config());
        $source->objectType('MPollSearchFormType', MPollSearchFormType::config());
        $source->objectType('MPollPollType', MPollPollType::config());

	    $source->queryType(MPollResultsQueryType::config());
        $source->queryType(MPollPageSearchType::config());
        $source->queryType(MPollPageFilterFormType::config());
        $source->queryType(MPollPagePollType::config());
    }

	public static function initCustomizer(BuilderConfig $config) {
        $templates = [
            'com_mpoll.mpoll' => [
                'label' => 'MPoll Searchable Results',
                'fieldset' => [
                    'default' => [
                        'fields' => [
                            'poll' => ($section = [
                                'label' => 'Limit by Poll',
                                'description' => 'The template is only assigned to a single poll/form.',
                                'type' => 'select',
                                'default' => [],
                                'options' => [['value'=>'','text'=>''],['evaluate' => 'yootheme.builder.mpoll_polls']],
                                'required' => true,
                                'attrs' => [
                                    'multiple' => true,
                                    'class' => 'uk-height-small',
                                ],
                            ]),
                        ],
                    ],
                ],
            ],
        ];


        $config->merge([
            'templates' => $templates,
            'mpoll_polls'=>
                array_map(function($poll) {
                    return ['value'=>$poll->poll_id,'text'=>$poll->poll_name];
                }, MPollProvider::pollList())
        ]);

    }
}
