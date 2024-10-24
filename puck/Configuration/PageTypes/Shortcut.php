<?php

return new \UBOS\Puck\Configuration\PageTypeConfiguration(
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