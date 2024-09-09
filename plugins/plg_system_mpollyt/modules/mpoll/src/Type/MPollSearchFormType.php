<?php

class MPollSearchFormType
{
    public static function config()
    {
        $config = [

            'fields' => [
                'mpollsearchform' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Form',
                        'arguments' => [
                            'buttonWidth' => [
                                'label' => 'Button Width',
                                'type' => 'select',
                                'default' => '1-6',
                                'options' => [
                                    '1/1'=>'1-1',
                                    '1/2'=>'1-2',
                                    '1/3'=>'1-3',
                                    '2/3'=>'2-3',
                                    '1/4'=>'1-4',
                                    '3/4'=>'3-4',
                                    '1/5'=>'1-5',
                                    '2/5'=>'2-5',
                                    '3/5'=>'3-5',
                                    '4/5'=>'4-5',
                                    '1/6'=>'1-6',
                                    '5/6'=>'5-6'
                                ]
                            ],
                            'buttonColor' => [
                                'label' => 'Button Color',
                                'type' => 'select',
                                'default' => 'default',
                                'options' => [
                                    'Default'=>'default',
                                    'Primary'=>'primary',
                                    'Secondary'=>'secondary'
                                ]
                            ],
                            'buttonAlign' => [
                                'label' => 'Button Align',
                                'type' => 'select',
                                'default' => 'left',
                                'options' => [
                                    'Left'=>'',
                                    'Center'=>'uk-align-center'
                                ]
                            ]
                        ],
                    ],
                    'extensions' => [
                        'call' => [
                            'func'=>__CLASS__ . '::resolve'
                        ]
                    ],
                    'args' => [
                        'buttonWidth' => [
                            'type' => 'String',
                        ],
                        'buttonColor' => [
                            'type' => 'String',
                        ],
                        'buttonAlign' => [
                            'type' => 'String',
                        ]
                    ],

                ]
            ],

            'metadata' => [
                'type' => true,
                'label' => 'MPoll Result Search Form'
            ]
        ];



        return $config;
    }

    public static function resolve($obj, $args, $context, $info)
    {
        $form = $obj['filterForm'];
        $questions = $obj['filterQuestions'];
        $html = '';
        $html .= '<form name="mpollform" id="mpollform" method="post" action="" enctype="multipart/form-data" class="uk-form">';
        $html .= '<div class="uk-grid-small" uk-grid>';


        $buttonWidth = $args['buttonWidth'];
        $buttonColor = $args['buttonColor'];
        $buttonAlign = $args['buttonAlign'];

        foreach ($questions as $qu) {
            //$html .= $sname.' - '.$qu->q_type;
            $html .= '<div class="uk-width-1-1 uk-width-'.$qu->q_filter_width.'@m">';
            if ($qu->q_type == 'gmap') {
                $html .= '<div class="uk-grid-small" uk-grid>';
                $html .= '<div class="uk-width-4-5">';
                $sname = 'qf_'.$qu->q_id;
                $html .= $form->getInput($sname,'filter');
                $html .= '</div>';
                $html .= '<div class="uk-width-1-5">';
                $snameDistance = 'qf_'.$qu->q_id.'_distance';
                $html .= $form->getInput($snameDistance,'filter');
                $html .= $fieldDistance->input;
                $html .= '</div>';
                $html .= '</div>';
            } else {
                $sname = 'qf_'.$qu->q_id;
                $html .= $form->getInput($sname,'filter');
            }
            $html .= '</div>';
        }

        /*$html .= '<pre>';
        $html .= print_r($obj,true);
        $html .= '</pre>';*/
        /*foreach($form->getGroup('filter') as $field) {
            $html .= '<div class="control-group">';
            $html .= '<div class="controls">'.$field->input.'</div>';
            $html .= '</div>';
        }*/
        $html .= '<div class="uk-width-1-1 uk-width-'.$buttonWidth.'@m '.$buttonAlign.'"><input name="submit" id="submit" value="Search" type="submit" class="uk-width-1-1 uk-button uk-button-'.$buttonColor.'"></div>';
        $html .= '</div>';
        $html .= JHtml::_( 'form.token' );
        $html .= '</form>';
        return $html;
    }






}
