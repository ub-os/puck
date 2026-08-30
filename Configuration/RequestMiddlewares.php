<?php

use UBOS\Puck\Middleware;

return [
	'frontend' => [
		'ubos/puck/slash-forcer' => [
			'target' => Middleware\SlashForcer::class,
			'before' => [
				'typo3/cms-workspaces/preview-permissions',
				'typo3/cms-frontend/tsfe',
			],
			'after' => [
				'typo3/cms-frontend/page-resolver',
				'typo3/cms-frontend/page-argument-validator',
			],
		],
	],
];
