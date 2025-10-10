<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'section_carousel',
	label: 'Section carousel',
	description: 'Carousel container that slides between section-level content elements.',
	icon: 'section_carousel',
	sorting: 90,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
        --div--;Advanced,
            options,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			]
		],
		'options' => [
			'label' => 'Carousel options',
		]
	],
	flexForms: [
		'options' => 'FILE:EXT:puck/Configuration/FlexForms/CarouselOptions.xml'
	],
	containerConfiguration: [
		[
			['name' => 'Content', 'colPos' => 300, 'allowed' => [
				'CType' => 'puck_text,puck_media,puck_cover_media,puck_hero,puck_row,puck_container'
			]]
		]
	],
);