<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'accordion',
	label: 'Accordion',
	description: 'Collapsible text and media element.',
	icon: 'accordion',
	sorting: 60,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridMedia,
            --palette--;;appearanceClasses,
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
		'assets' => \UBOS\Puck\Utility\TcaUtility::configOverrideWithBreakpointCropVariants(),
	]
);
