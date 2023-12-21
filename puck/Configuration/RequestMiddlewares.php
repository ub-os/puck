<?php

return [
    'frontend' => [
        'ubos/puck/redirect-doktypes' => [
            'target' => \UBOS\Puck\Middleware\RedirectDoktypes::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
            'before' => [
                'kitzberger/cms-redirects/page-url-resolved',
            ],
        ],
    ],
];