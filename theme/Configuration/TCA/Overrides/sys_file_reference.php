<?php

$GLOBALS['TCA']['sys_file_reference']['columns']['crop']['config']['cropVariants'] = [
    'default' => [
        'title' => 'Default',
        'allowedAspectRatios' => [
            'free' => [
                'title' => 'free',
                'value' => 'NaN'
            ],
            '3:2' => [
                'title' => '3:2',
                'value' => 3 / 2
            ],
            '16:9' => [
                'title' => '16:9',
                'value' => 16 / 9
            ],
            '5:2' => [
                'title' => '5:2',
                'value' => 5 / 2
            ],
            '2:1' => [
                'title' => '2:1',
                'value' => 2
            ],
            '1:1' => [
                'title' => '1:1',
                'value' => 1
            ],
        ]
    ],
    '1:1' => [
        'title' => '1:1',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '1:1',
                'value' => 1
            ]
        ]
    ],
    '3:2' => [
        'title' => '3:2',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '3:2',
                'value' => 3 / 2
            ]
        ]
    ],
    '2:1' => [
        'title' => '2:1',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '2:1',
                'value' => 2 / 1
            ]
        ]
    ],
    '5:2' => [
        'title' => '5:2',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '5:2',
                'value' => 5 / 2
            ]
        ]
    ]
];