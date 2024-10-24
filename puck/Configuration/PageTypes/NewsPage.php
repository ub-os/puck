<?php

return new \UBOS\Puck\Configuration\PageTypeConfiguration(
    16503,
    label: 'News Page',
    icon: 'news_page',
    showItemAdditions: [
        [
            '--div--;Teaser, --palette--;;teaser',
            'after:--palette--;;title'
        ],
        [
            'url;Redirect to URL, --palette--;;author',
            'after:--palette--;;title'
        ]
    ],
    columnsOverrides: [
        'post_date' => [
            'config' => [
                'required' => 1,
            ]
        ],
        'url' => [
            'config' => [
                'required' => 0
            ]
        ],
    ]
);