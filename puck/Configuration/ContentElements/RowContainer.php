<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'row_container',
	label: 'Row container',
	description: 'Container for text, media and cards. Flexible alignment, individual column widths and carousel variant.',
	icon: 'row_container',
	sorting: 80,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridColumnsAlignment,
        --div--;Advanced,
            options,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			]
		],
		'layout' => [
			'config' => [
				'items' => [
					['label' => 'Rows', 'value' => 'default', 'icon' => 'row_layout_row'],
					['label' => 'Carousel', 'value' => 'carousel', 'icon' => 'row_layout_carousel'],
				],
			]
		],
		'options' => [
			'label' => 'Carousel options',
			'displayCond' => 'FIELD:layout:IN:carousel',
		]
	],
	flexForms: [
		'options' => 'FILE:EXT:puck/Configuration/FlexForms/CarouselOptions.xml'
	],
	containerConfiguration: [
		[
			['name' => 'Content', 'colPos' => 600, 'allowed' => ['CType' => 'puck_text, puck_media, puck_card']]
		]
	],
);