<?php
$ratios = [
    'free' => [
        'title' => 'free',
        'value' => 'NaN'
    ],
    '1:1' => [
        'title' => '1:1',
        'value' => 1
    ],
    '2:1' => [
        'title' => '2:1',
        'value' => 2
    ],
    '3:1' => [
        'title' => '3:1',
        'value' => 3
    ],
    '3:2' => [
        'title' => '3:2',
        'value' => 3 / 2
    ],
    '4:1' => [
        'title' => '4:1',
        'value' => 4
    ],
    '4:3' => [
        'title' => '4:3',
        'value' => 4 / 3
    ],
    '5:2' => [
        'title' => '5:2',
        'value' => 5 / 2
    ],
    '5:3' => [
        'title' => '5:3',
        'value' => 5 / 3
    ],
    '5:4' => [
        'title' => '5:4',
        'value' => 5 / 4
    ],
    '16:9' => [
        'title' => '16:9',
        'value' => 16 / 9
    ],
    '16:10' => [
        'title' => '16:10',
        'value' => 16 / 10
    ]
];
$variants = [
    'default' => [
        'title' => 'Default',
        'allowedAspectRatios' => $ratios,
    ],
    'mobile' => [
        'title' => 'Mobile',
        'allowedAspectRatios' => $ratios,
    ],
];
foreach($ratios as $key => $ratio) {
    if (count(explode(':', $key)) === 2) {
        $variants[$key] = [
            'title' => $key,
            'allowedAspectRatios' => [
                'default' => $ratio
            ]
        ];
    }
}
return $variants;