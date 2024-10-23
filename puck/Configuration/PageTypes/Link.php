<?php

return new \UBOS\Puck\PageTypeDefinition(
    3,
    showItemAdditions: [
        [
            '--palette--;;standard',
            'replace:doktype'
        ],
        [
            '--div--;Teaser, --palette--;;teaser',
            'after:--palette--;;external'
        ]
    ],
);