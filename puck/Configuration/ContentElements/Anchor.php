<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'anchor',
	label: 'Anchor',
	description: 'Anchor for anchor menu',
	group: '02_menu',
	icon: 'anchor',
	sorting: 140,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header, 
            subheader,',
	columnsOverrides: [
		'header' => [
			'label' => 'Title'
		],
		'subheader' => [
			'label' => 'URL Segment',
			'displayCond' => 'FIELD:CType:=:puck_anchor',
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