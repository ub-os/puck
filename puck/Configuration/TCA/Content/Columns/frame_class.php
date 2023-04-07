<?php

return [
    'label' => 'Background',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'disableNoMatchingValueElement' => true,
        'items' => [
            ['Default', 'default'],
            ['Light blue', 'light-1'],
            ['Light turquoise', 'light-2'],
            ['Light orange', 'light-3'],
            ['Dark purple', 'dark-1'],
        ],
        'default' => 'default'
    ],
];
