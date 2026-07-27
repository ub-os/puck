<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'file_menu',
	label: 'File menu',
	description: 'Menu of downloadable files.',
	group: '02_menu',
	icon: 'file_menu',
	sorting: 120,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearanceLayout,
            --palette--;;headers,
        --div--;Layout,
            --palette--;;gridContainer,
            item_column_width,
            --palette--;;appearanceOptions,
        --div--;Files,
            --palette--;;fileMenu,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			]
		]
	],
	pluginName: 'FileMenu',
);