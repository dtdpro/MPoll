<?php

class MPollResultsType
{
    public static function config()
    {
        $config = [

            'fields' => [
                'cm_id' => [
                    'type' => 'String',
                    'metadata' => [
                        'label' => 'Result Id'
                    ]
                ]


            ],

            'metadata' => [
                'type' => true,
                'label' => 'MPoll Result'
            ]

        ];

        $questions = MPollProvider::getQuestions();

        foreach($questions as $index=>$question) {
            switch ($question->q_type) {
                case 'textbox':
                //default:
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
            }
        }

        return $config;
    }

    public static function resolveResult($obj,$args) {
        $fn = 'q_'.$args['id'];
        switch ($args['type']) {
            case 'textbox':
                $data = $obj->$fn;
                break;
        }
        return $data;
    }



}
