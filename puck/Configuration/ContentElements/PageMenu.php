<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'page_menu',
	label: 'Page menu',
	description: 'Plugin to display a menu of pages. Flexible layout options.',
	group: '02_menu',
	icon: 'page_menu',
	sorting: 110,
	showItem: '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Menu settings,
            pi_flexform,
        --div--;Layout,
            --palette--;;gridContainer,
            --palette--;;gridMenuPages,
            menu_item_config,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			]
		],
		'layout' => [
			'config' => [
				'items' => [
					['label' => 'Default cards', 'value' => 'default-cards'],
					['label' => 'Blog cards', 'value' => 'blog-cards'],
					['label' => 'Team cards', 'value' => 'team-cards'],
					['label' => 'Cards (custom settings)', 'value' => 'cards'],
					['label' => 'Columns (custom settings)', 'value' => 'columns'],
				],
				'default' => 'default-cards'
			]
		],
		'pi_flexform' => [
			'label' => 'Menu settings'
		],
		'flex_grow' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'menu_item_config' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'item_column_width' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'media_column_width' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'row_justify' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'row_align' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'media_layout' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'container_width' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'container_offset' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'container_position' => [
			'displayCond' => 'FIELD:layout:IN:cards,columns',
		],
		'card_media_size' => [
			'displayCond' => 'FIELD:layout:=:cards',
		],
		'text_column_width' => [
			'displayCond' => 'FIELD:layout:=:columns',
		],
	],
	pluginName: 'PageMenu',
	flexForms: ['pi_flexform' => 'FILE:EXT:puck/Configuration/FlexForms/PageMenu.xml'],
	previewRenderer: \UBOS\Puck\Backend\PageMenuPreviewRenderer::class,
);