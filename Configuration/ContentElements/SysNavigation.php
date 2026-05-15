<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'sys_navigation',
	label: 'Navigation',
	description: 'Configure a navigation element',
	group: '02_menu',
	icon: 'sys_navigation',
	sorting: 1,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header, 
            subheader,
            layout,
            pages',
	columnsOverrides: [
		'header' => [
			'label' => 'Label'
		],
		'layout' => [
			'label' => 'Mode',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['label' => 'Subpages of selected pages', 'value' => 'tree'],
					['label' => 'Selected pages', 'value' => 'list'],
				],
			],
		],
		'subheader' => [
			'label' => 'Identifier',
			'config' => [
				'type' => 'slug',
				'generatorOptions' => [
					'fields' => ['header'],
					'fieldSeparator' => '-',
					'replacements' => [
						'/' => '',
					],
				],
				'appearance' => [
					'prefix' => \UBOS\Puck\UserFunc\FormEngine\Tca::class . '->getHash',
				],
				'fallbackCharacter' => '-',
				'eval' => 'uniqueInPid',
				'default' => '',
			],
		],
	]
);