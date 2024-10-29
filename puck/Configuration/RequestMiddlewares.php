<?php

return [
    'frontend' => [
        'ubos/puck/redirect-doktypes' => [
            'target' => \UBOS\Puck\Middleware\RedirectDoktypes::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
            'before' => [
                'typo3/cms-frontend/shortcut-and-mountpoint-redirect',
            ],
        ],
        'ubos/puck/slash-forcer' => [
            'target' => \UBOS\Puck\Middleware\SlashForcer::class,
            'before' => [
                'typo3/cms-workspaces/preview-permissions',
                'typo3/cms-frontend/tsfe',
            ],
            'after' => [
                'typo3/cms-frontend/page-resolver',
                'typo3/cms-frontend/page-argument-validator',
            ],
        ],
        'ubos/puck/favicon' => [
            'target' => \UBOS\Puck\Middleware\Favicon::class,
            'after' => [
                'typo3/cms-frontend/static-route-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];