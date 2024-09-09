<?php

class MPollResultsType
{
    public static function config()
    {

        $questions = MPollProvider::getQuestions();
        $config = [

            'fields' => [
                'cm_id' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Result Id'
                    ]
                ],
                'featured' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Featured Status'
                    ]
                ],
                'debug' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Debug Information'
                    ],
                    'extensions' => [
                        'call' => [
                            'func'=>__CLASS__ . '::resolveDebug'
                        ]
                    ]
                ]
            ],

            'metadata' => [
                'type' => true,
                'label' => 'MPoll Result'
            ]
        ];

        foreach($questions as $index=>$question) {
            switch ($question->q_type) {
                case 'textbox':
                case 'textar':
                case 'email':
                case 'datedropdown':
                    $newField = [
                        'type' => 'String',
                        'metadata' => [
                            'label' => $question->poll_name.' ('.$question->q_poll.'): '.$question->q_name
                        ],
                        'extensions' => [
                            'call' => [
                                'func'=>__CLASS__ . '::resolveResult',
                                'args'=>[
                                    'id'=>$question->q_id,
                                    'type'=>$question->q_type
                                ]
                            ]
                        ]
                    ];
                    $fn = 'q_'.$question->q_id;
                    $config['fields'][$fn] = $newField;
                    break;
                case 'gmap':
                    // Address
                    $newField = [
                        'type' => 'String',
                        'metadata' => [
                            'label' => $question->poll_name.' ('.$question->q_poll.'): '.$question->q_name
                        ],
                        'extensions' => [
                            'call' => [
                                'func'=>__CLASS__ . '::resolveResult',
                                'args'=>[
                                    'id'=>$question->q_id,
                                    'type'=>$question->q_type
                                ]
                            ]
                        ]
                    ];
                    $fn = 'q_'.$question->q_id;
                    $config['fields'][$fn] = $newField;
                    // Lat & Lon
                    $newField = [
                        'type' => 'String',
                        'metadata' => [
                            'label' => $question->poll_name.' ('.$question->q_poll.'): '.$question->q_name.' Coordinates'
                        ],
                        'extensions' => [
                            'call' => [
                                'func'=>__CLASS__ . '::resolveResulOtherAndAlt',
                                'args'=>[
                                    'id'=>$question->q_id,
                                    'type'=>$question->q_type
                                ]
                            ]
                        ]
                    ];
                    $fno = 'q_'.$question->q_id.'_latlon';
                    $config['fields'][$fno] = $newField;
                    break;
                case 'dropdown':
                case 'multi':
                    $newField = [
                        'type' => 'String',
                        'metadata' => [
                            'label' => $question->poll_name.' ('.$question->q_poll.'): '.$question->q_name
                        ],
                        'extensions' => [
                            'call' => [
                                'func'=>__CLASS__ . '::resolveResultOption',
                                'args'=>[
                                    'id'=>$question->q_id,
                                    'type'=>$question->q_type,
                                    'options'=>$question->options
                                ]
                            ]
                        ]
                    ];
                    $fn = 'q_'.$question->q_id;
                    $config['fields'][$fn] = $newField;
                    break;
                case 'mlist':
                case 'mcbox':
                    $newField = [
                        'type' => [
                            'listOf' =>'MPollQuestionOptionType'
                        ],
                        'metadata' => [
                            'label' => $question->poll_name.' ('.$question->q_poll.'): '.$question->q_name
                        ],
                        'extensions' => [
                            'call' => [
                                'func'=>__CLASS__ . '::resolveResultOptions',
                                'args'=>[
                                    'id'=>$question->q_id,
                                    'type'=>$question->q_type,
                                    'options'=>$question->options
                                ]
                            ]
                        ]
                    ];
                    $fn = 'q_'.$question->q_id;
                    $config['fields'][$fn] = $newField;
                    break;
                case 'cbox':
                    $newField = [
                        'type' => 'String',
                        'metadata' => [
                            'label' => $question->poll_name.' ('.$question->q_poll.'): '.$question->q_name
                        ],
                        'extensions' => [
                            'call' => [
                                'func'=>__CLASS__ . '::resolveResultCheckbox',
                                'args'=>[
                                    'id'=>$question->q_id,
                                    'type'=>$question->q_type
                                ]
                            ]
                        ]
                    ];
                    $fn = 'q_'.$question->q_id;
                    $config['fields'][$fn] = $newField;
                    break;
            }
        }

        return $config;
    }

    public static function resolveDebug($obj, $args, $context, $info)
    {
        return print_r($obj,true).print_r($args,true);
    }


    public static function resolveResult($obj,$args) {
        $fn = 'q_'.$args['id'];
        $data = '';
        switch ($args['type']) {
            case 'textbox':
            case 'gmap':
            case 'textar':
            case 'email':
            case 'datedropdown':
                $data = $obj->$fn;
                break;
        }
        return $data;
    }

    public static function resolveResultOption($obj,$args) {
        $fn = 'q_'.$args['id'];
        $data = '';
        switch ($args['type']) {
            case 'dropdown':
            case 'multi':
                $opts = $args['options'];
                $opt = $opts[$obj->$fn];
                $data = $opt['opt_txt'];
                break;
        }
        return $data;
    }

    public static function resolveResultCheckbox($obj,$args) {
        $fn = 'q_'.$args['id'];
        $data = 'No';
        switch ($args['type']) {
            case 'cbox':
                if ($obj->$fn == "1") {
                    $data ='Yes';
                }
                break;
        }
        return $data;
    }

    public static function resolveResultOptions($obj,$args) {
        $fn = 'q_'.$args['id'];
        $resOpts = [];
        switch ($args['type']) {
            case 'mlist':
            case 'mcbox':
                $results = explode(" ",$obj->$fn);
                $opts = $args['options'];
                foreach ($opts as $opt) {
                    if (in_array($opt['opt_id'],$results)) {
                        $resOpts[] = $opt;
                    }
                }
                break;
        }
        return $resOpts;
    }

    public static function resolveResultOther($obj,$args) {
        $fno = 'q_'.$args['id'].'_other';
        switch ($args['type']) {
            case 'gmap':
                $data = $obj->$fno;
                break;
        }
        return $data;
    }

    public static function resolveResulAlt($obj,$args) {
        $fna = 'q_'.$args['id'].'_other_alt';
        switch ($args['type']) {
            case 'gmap':
                $data = $obj->$fna;
                break;
        }
        return $data;
    }

    public static function resolveResulOtherAndAlt($obj,$args) {
        $fno = 'q_'.$args['id'].'_other';
        $fna = 'q_'.$args['id'].'_other_alt';
        switch ($args['type']) {
            case 'gmap':
                $data = $obj->$fno.', '.$obj->$fna;
                break;
        }
        return $data;
    }



}
