<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'text',
	label: 'Text',
	description: 'Simple text element.',
	icon: 'text',
	sorting: 0,
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
            --palette--;;bodytext,
        --div--;Layout,
            --palette--;;gridContainer,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			]
		]
	],
);