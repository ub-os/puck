<?php

return new \UBOS\Puck\PageTypeDefinition(
    4,
    showItemAdditions: [
        [
            '--palette--;;standard',
            'replace:doktype'
        ],
        [
            '--div--;Teaser, --palette--;;teaser',
            'after:--palette--;;shortcutpage'
        ]
    ],
);