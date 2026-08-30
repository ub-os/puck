<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'error_text',
	label: 'Error text',
	description: 'Error page message.',
	icon: 'error_text',
	showItem: '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            header,
            subheader,
            --palette--;;bodytext,',
	columnsOverrides: [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			],
		],
	]
);
