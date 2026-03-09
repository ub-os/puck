<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\UserFunc\FormEngine\ContentItemsProcFunc;
use UBOS\Puck\Utility\TcaUtility;

$columns = [];

$columns['frame_class'] = [
	'label' => 'Background',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'disableNoMatchingValueElement' => true,
		'items' => [
			['Default', 'default'],
			['Alternative 1', 'dark-1'],
		],
		'default' => 'default'
	],
];

$columns['layout'] = [
	'label' => 'Variant',
	'onChange' => 'reload',
	'disableNoMatchingValueElement' => true,
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'items' => [
			['Default', 'default'],
		],
		'disableNoMatchingValueElement' => true,
		'default' => 'default'
	],
];

$columns['header_layout'] = [
	'label' => 'Headline Type',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'disableNoMatchingValueElement' => true,
		'items' => [
//			// 0 = default
//			['label' => 'H2', 'value' => 0, 'group' => 'h2'],
//			// 20-29 reserved for h2 styles, these styles increase spacing to preceding element
//			// for example: ['H2 alternative color', 21],
//
//			// 25-29 reserved for h2 styles that enable subheader
//			['label' => 'H2 in topline style (with subheader)', 'value' => 25, 'group' => 'h2'],
//
//			// 30-39 reserved for headlines in h3 style, these styles do NOT increase spacing to preceding element
//			['label' => 'H3', 'value' => 30, 'group' => 'h3'],
//			['label' => 'H2 in H3-style', 'value' => 31, 'group' => 'h2'],
//
//			['label' => 'Paragraph', 'value' => 50, 'group' => 'other'],
//
//			// 90-99 reserved for screen reader only
//			['label' => 'H2 screen reader only', 'value' => 90, 'group' => 'h2'],
//			['label' => 'H3 screen reader only', 'value' => 91, 'group' => 'h3'],
//
//			// 100 is hidden and for backend only
//			['label' => 'Hidden (backend only)', 'value' => 100, 'group' => 'other'],

			['label' => 'H2', 'value' => 'h2.h2', 'group' => '<h2>'],
			['label' => 'H2 (subheader as topline)', 'value' => 'h2.h2.topline', 'group' => '<h2>'],
			['label' => 'Topline-H2 (subheader as headline)', 'value' => 'h2.topline.h2', 'group' => '<h2>'],
			['label' => 'H3 (<h2>)', 'value' => 'h2.h3', 'group' => '<h2>'],
			['label' => 'H2 (screen reader only)', 'value' => 'h2.sr-only', 'group' => '<h2>'],

			['label' => 'H3', 'value' => 'h3.h3', 'group' => '<h3>'],
			['label' => 'H2 (<h3>)', 'value' => 'h3.h2', 'group' => '<h3>'],
			['label' => 'H3 (screen reader only)', 'value' => 'h3.sr-only', 'group' => '<h3>'],

			['label' => 'H2 (<p>)', 'value' => 'p.h2', 'group' => '<p>'],
			['label' => 'H3 (<p>)', 'value' => 'p.h2', 'group' => '<p>'],

			['label' => 'Hidden (backend only)', 'value' => 100, 'group' => 'hidden'],
		],
		'itemGroups' => [
			'<h2>' => '<h2>',
			'<h3>' => '<h3>',
			'<p>' => '<p>',
			'hidden' => 'hidden'
		],
		'default' => 'h2.h2'
	],
];
$columns['subheader'] = [
	'label' => $GLOBALS['TCA']['tt_content']['columns']['subheader']['label'],
	'config' => $GLOBALS['TCA']['tt_content']['columns']['subheader']['config'],
//	'displayCond' => [
//		'AND' => [
//			'FIELD:header_layout:!=:100',
//			'FIELD:header_layout:>:24',
//			'FIELD:header_layout:<:30',
//		],
//	],
];

$columns['bodytext'] = $GLOBALS['TCA']['tt_content']['columns']['bodytext'];
$columns['bodytext']['config']['search']['andWhere'] = '';

$columns['imagecols'] = [
	'label' => 'Media per Row',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'disableNoMatchingValueElement' => true,
		'items' => [
			['1', 1],
			['2', 2],
			['3', 3],
			['4', 4],
			['5', 5],
		],
		'default' => 1
	],
];
$columns['media_layout'] = [
	'label' => 'Media layout',
	'onChange' => 'reload',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'disableNoMatchingValueElement' => true,
		'items' => [
			['Below text', 'below',
				'media_layout_below',
			],
			['Above text', 'above',
				'media_layout_above',
			],
			['Right beside text', 'right',
				'media_layout_right',
			],
			['Left beside text', 'left',
				'media_layout_left',
			],
			['Right in text', 'right-float',
				'media_layout_right_float',
			],
			['Left in text', 'left-float',
				'media_layout_left_float'
			],
		],
		'itemsProcFunc' => ContentItemsProcFunc::class . '->mediaLayout',
		'dbFieldLength' => 255,
		'default' => 'below',
		'fieldWizard' => [
			'selectIcons' => [
				'disabled' => false,
			],
		],
	]
];

$columns['pages'] = [
	'label' => 'Pages',
	'config' => [
		'type' => 'group',
		'allowed' => 'pages',
		'size' => 3,
		'maxitems' => 50
	],
];

$columns['header_spacing_override'] = [
	'label' => 'Force spacing',
	'description' => 'Force increased distance to preceding element, even if no h2-style headline is set.',
	'config' => [
		'type' => 'check',
	],
];

$columns['space_before_class'] = [
	'label' => 'Space Before',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'items' => [
			['Auto', '',
				'auto'
			],
			['None', 'none',
				'none'
			],
			['Small', 'small',
				'space_small'
			],
			['Medium', 'medium',
				'space_medium'
			],
			['Large', 'large',
				'space_large'
			],
		],
		'default' => '',
	]
];
$columns['space_after_class'] = [
	'label' => 'Space After',
	'config' => $columns['space_before_class']['config']
];
$columns['assets'] = $GLOBALS['TCA']['tt_content']['columns']['assets'];
$columns['assets']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [
	'default' => TcaUtility::getCropVariant('Default', 'standard'),
];

$columns['icon'] = require ExtensionManagementUtility::extPath('puck') . '/Configuration/TCA/Helper/IconField.php';

$columnWidthItems = [
	['12', 12, 'column_width12'],
	['11', 11, 'column_width11'],
	['10', 10, 'column_width10'],
	['9', 9, 'column_width9'],
	['8', 8, 'column_width8'],
	['7', 7, 'column_width7'],
	['6', 6, 'column_width6'],
	['5', 5, 'column_width5'],
	['4', 4, 'column_width4'],
	['3', 3, 'column_width3'],
	['2', 2, 'column_width2'],
];

$columns['container_width'] = [
	'label' => 'Width',
	'onChange' => 'reload',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'disableNoMatchingValueElement' => true,
		'itemsProcFunc' => ContentItemsProcFunc::class . '->containerWidth',
		'items' => $columnWidthItems,
		'default' => 12,
		'fieldWizard' => [
			'selectIcons' => [
				'disabled' => false,
			],
		],
	],
];
$columns['item_column_width'] = [
	'label' => 'Row item width',
	'onChange' => 'reload',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'itemsProcFunc' => ContentItemsProcFunc::class . '->itemColumnWidth',
		'disableNoMatchingValueElement' => true,
		'items' => $columnWidthItems,
		'default' => 6,
		'fieldWizard' => [
			'selectIcons' => [
				'disabled' => false,
			],
		],
	],
];

$columns['text_column_width'] = $columns['item_column_width'];
$columns['media_column_width'] = $columns['item_column_width'];
$columns['text_column_width']['label'] = 'Text width';
$columns['media_column_width']['label'] = 'Media width';
$columns['text_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class . '->textColumnWidth';
$columns['media_column_width']['config']['itemsProcFunc'] = ContentItemsProcFunc::class . '->mediaColumnWidth';

$columns['row_justify'] = [
	'label' => 'Align horizontally',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'disableNoMatchingValueElement' => true,
		'items' => [
			['Left', 'left', 'align_left'],
			['Center', 'center', 'align_center'],
			['Right', 'right', 'align_right'],
			['Space between', 'space-between', 'align_space_between'],
		],
		'default' => 'left',
		'fieldWizard' => [
			'selectIcons' => [
				'disabled' => false,
			],
		],
	],
];
$columns['row_align'] = [
	'label' => 'Align vertically',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'disableNoMatchingValueElement' => true,
		'items' => [
			['Top', 'start', 'align_top'],
			['Center', 'center', 'align_center_vertical'],
			['Bottom', 'end', 'align_bottom'],
			['Stretch', 'stretch', 'align_stretch']
		],
		'default' => 'top',
		'fieldWizard' => [
			'selectIcons' => [
				'disabled' => false,
			],
		],
	],
];
$columns['container_position'] = [
	'label' => 'Position',
	'onChange' => 'reload',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'disableNoMatchingValueElement' => true,
		'items' => [
			['Left', 'left', 'align_left'],
			['Center', 'center', 'align_center'],
			['Right', 'right', 'align_right'],
		],
		'default' => 'center',
		'fieldWizard' => [
			'selectIcons' => [
				'disabled' => false,
			],
		],
	],
];
$columns['container_offset'] = [
	'label' => 'Offset',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'itemsProcFunc' => ContentItemsProcFunc::class . '->containerOffset',
		'disableNoMatchingValueElement' => true,
		'items' => [
			['0', 0],
			['1', 1],
			['2', 2],
			['3', 3],
			['4', 4]
		],
		'default' => 0,
	],
];
$columns['card_media_size'] = [
	'label' => 'Media size',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'disableNoMatchingValueElement' => true,
		'items' => [
			['Full-width / full-height', 'cover', 'size_cover'],
			['Contained', 'contain', 'size_contain'],
			['Background', 'background'],
		],
		'default' => 'cover',
		'fieldWizard' => [
			'selectIcons' => [
				'disabled' => false,
			],
		],
	]
];
$columns['media_max_height'] = [
	'label' => 'Maximum height',
	'description' => 'Maximum image height for mobile devices (in % of viewport height)',
	'config' => [
		'type' => 'number',
		'size' => 30,
		'default' => 0,
		'range' => [
			'lower' => 0,
			'upper' => 100
		],
		'valuePicker' => [
			'items' => [
				['20', 20],
				['30', 30],
				['40', 40],
				['50', 50]
			],
		],
	],
];
$columns['menu_item_config'] = [
	'label' => 'Teaser content',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectMultipleSideBySide',
		'items' => [
			['Teaser text', 'teaserText'],
			['Subtitle', 'subtitle'],
			['Media', 'media'],
			['Category', 'category'],
			['Category List', 'categoryList'],
			['Author', 'author'],
			['Last Update (lastUpdated)', 'lastUpdated'],
			['Post Date (post_date)', 'postDate'],
			['Page Icon', 'icon'],
			['Call to action', 'cta'],
			['Arrow / Link Symbol', 'arrow']
		],
		'default' => '',
	]
];
$columns['flex_grow'] = [
	'label' => 'Grow items',
	'description' => 'Items will grow to fill the available space',
	'config' => [
		'type' => 'check',
		'renderType' => 'checkboxToggle',
		'default' => 0,
	]
];
$columns['options'] = [
	'label' => 'Options',
	'config' => [
		'type' => 'flex',
		'ds' => [
			'default' => $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds']['default'],
		],
		'ds_pointerField' => 'layout,CType',
	]
];
foreach ($columns as $name => $column) {
	$GLOBALS['TCA']['tt_content']['columns'][$name] = $column;
}