<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'media',
	label: 'Text and media',
	description: 'Flexible text and media layouts.',
	icon: 'media',
	sorting: 10,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridMedia,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			],
		],
		'item_column_width' => [
			'label' => 'Media item width',
		],
		'assets' => \UBOS\Puck\Utility\TcaUtility::configOverrideWithBreakpointCropVariants()
	]
);