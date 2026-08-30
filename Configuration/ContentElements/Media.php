<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'media',
	label: 'Text & media',
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
            --palette--;;appearanceOptions,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			],
		],
		// colPos < 600 is for section-level elements, >= 600 for container child elements
		'frame_class' => [
			'displayCond' => 'FIELD:colPos:<:600',
		],
		'header_spacing_override' => [
			'displayCond' => 'FIELD:colPos:<:600',
		],
		'container_position' => [
			'displayCond' => 'FIELD:colPos:<:600',
		],
		'container_offset' => [
			'displayCond' => 'FIELD:colPos:<:600',
		],
		'item_column_width' => [
			'label' => 'Media element width',
		],
		'assets' => \UBOS\Puck\Utility\TcaUtility::configOverrideWithBreakpointCropVariants(),
	]
);
