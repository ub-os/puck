<?php

$GLOBALS['TCA']['tt_content']['types']['shape_form'] = [
	'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;appearance,
            --palette--;;headers,
			pi_flexform,
        --div--;Layout,
            --palette--;;gridContainer,',
	'columnsOverrides' => [
		'bodytext' => [
			'config' => [
				'enableRichtext' => true,
			],
		],
		'item_column_width' => [
			'label' => 'Media item width',
		],
	]
];