<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'card',
	label: 'Card',
	description: 'Card with flexible layout.',
	icon: 'card',
	sorting: 50,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridCard,
        --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.media,
            assets,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			]
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
		'assets' => \UBOS\Puck\Utility\TcaUtility::configOverrideWithBreakpointCropVariants()
	]
);