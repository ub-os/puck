<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'hero',
	label: 'Hero',
	description: 'Page introduction with h1-headline.',
	icon: 'hero',
	sorting: 30,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;layout,
            --palette--;;header_header_position,
            --palette--;;bodytext,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			],
		],
		'assets' => \UBOS\Puck\Utility\TcaUtility::configOverrideWithCropVariants('2:1,3:2')
	]
);