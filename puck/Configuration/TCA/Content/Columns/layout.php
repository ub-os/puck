<?php

return [
    'label' => 'Appearance type',
    'onChange' => 'reload',
    'disableNoMatchingValueElement' => true,
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['Default', 'default'],
        ],
        'disableNoMatchingValueElement' => true,
        'default' => 'default'
    ],
];
