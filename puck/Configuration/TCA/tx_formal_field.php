<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use UBOS\Puck\Utility\TcaUtility;

$ctrl = [
	'label' => 'label',
	'title' => 'Form field',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'versioningWS' => true,
	'languageField' => 'sys_language_uid',
	'transOrigPointerField' => 'l10n_parent',
	'transOrigDiffSourceField' => 'l10n_diffsource',
	'iconfile' => 'EXT:puck/Resources/Public/Icons/Backend/Default.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'label',
	'type' => 'type',
	'typeicon_column' => 'type',
	'typeicon_classes' => [
		'text' => 'form-text',
		'textarea' => 'form-textarea',
		'select' => 'form-single-select',
		'single-checkbox' => 'form-checkbox',
		'checkbox' => 'form-multi-checkbox',
		'radio' => 'form-radio-button',
		'email' => 'form-email',
		'number' => 'form-number',
		'tel' => 'form-telephone',
		'password' => 'form-password',
		'url' => 'form-url',
		'color' => 'default',
		'range' => 'form-text',
		'date' => 'form-date-picker',
		'file' => 'form-file-upload',
		'reset' => 'default',
		'captcha' => 'default',
		'country' => 'default',
		'hidden' => 'form-hidden',
		'rte' => 'form-static-text',
		'content' => 'form-content-element',
		'repeatable-container' => 'default',
	],

];
$interface = [];
$columns = [
	'label' => [
		'label' => 'Label',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
			'required' => true,
		],
	],
	'description' => [
		'label' => 'Description',
		'config' => [
			'type' => 'text',
			'rows' => 2,
		],
	],
	'placeholder' => [
		'label' => 'Placeholder',
		'displayCond' => 'FIELD:type:IN:text,textarea,email,number,tel,password,url',
		'config' => [
			'type' => 'text',
			'rows' => 2,
		],
	],
	'type' => [
		'label' => 'Type',
		'onChange' => 'reload',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
				['Text field', 'text', 'form-text', 'basic'],
				['Textarea', 'textarea', 'form-textarea', 'basic'],
				['Select', 'select', 'form-single-select', 'basic'],
				['Single checkbox', 'single-checkbox', 'form-checkbox', 'basic'],
				['Checkboxes', 'checkbox', 'form-multi-checkbox', 'basic'],
				['Radio buttons', 'radio', 'form-radio-button', 'basic'],
				['Email', 'email', 'form-email', 'typed-text-inputs'],
				['Number', 'number', 'form-number', 'typed-text-inputs'],
				['Phone number', 'tel', 'form-telephone', 'typed-text-inputs'],
				['Password', 'password', 'form-password', 'typed-text-inputs'],
				['URL', 'url', 'form-url', 'typed-text-inputs'],
				['Color', 'color', 'default', 'special'],
				['Range', 'range', 'default', 'special'],
				['Date', 'date', 'form-date-picker', 'special'],
				['File', 'file', 'form-file-upload', 'special'],
				['Reset', 'reset', 'default', 'special'],
				['Captcha', 'captcha', 'default', 'special'],
				['Country select', 'country', 'default', 'special'],
				['Hidden input', 'hidden', 'form-hidden', 'special'],
				['Rich text content', 'rte', 'form-static-text', 'no-input'],
				['Content element', 'content', 'form-content-element', 'no-input'],
				['Repeatable fields', 'repeatable-container', 'default', 'special'],

			]),
			'itemGroups' => [
				'basic' => 'Basic',
				'typed-text-inputs' => 'Typed text fields',
				'special' => 'Special',
				'no-input' => 'Content only / No input',
			]
		],
	],
	'default_value' => [
		'label' => 'Default value',
		'displayCond' => 'FIELD:type:!IN:select,checkbox,radio',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'default' => null,
		],
	],
	'fieldset' => [
		'label' => 'Fieldset',
		'config' => [
			'type' => 'group',
			'allowed' => 'tx_formal_step,tx_formal_field',
			'minitems' => 0,
			'maxitems' => 1,
		],
	],
	'fields' => [
		'label' => 'Fields',
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_formal_field',
			'foreign_field' => 'fieldset',
			'foreign_sortby' => 'sorting',
			'appearance' => [
				'expandSingle' => true,
				'useSortable' => true
			],
		],
	],
	'identifier' => [
		'label' => 'Identifier',
		'config' => [
			'type' => 'slug',
			'generatorOptions' => [
				'fields' => ['label'],
				'fieldSeparator' => '-',
				'replacements' => [ '/' => '' ],
			],
			'appearance' => [
				'prefix' => \UBOS\Puck\UserFunctions\FormEngine\SlugPrefix::class . '->getHash',
			],
			'fallbackCharacter' => '-',
			'eval' => 'uniqueInPid',
			'default' => '',
		],
	],
	'required' => [
		'label' => 'Required',
		'config' => [
			'type' => 'check',
		],
	],
	'field_options' => [
		'label' => 'Options',
		'displayCond' => 'FIELD:type:IN:select,checkbox,radio',
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_formal_field_option',
			'foreign_field' => 'field',
			'foreign_sortby' => 'sorting',
			'appearance' => [
				'expandSingle' => true,
				'useSortable' => true
			],
		],
	],
	'validation' => [
		'label' => 'Validation',
		'onChange' => 'reload',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
				['Auto', ''],
				['Length', 'length'],
				['RegEx', 'regex'],
			]),
		],
	],
	'validation_config' => [
		'label' => 'Validation Configuration',
		'displayCond' => 'FIELD:validation:IN:length,regex',
		'config' => [
			'type' => 'input',
		],
	],
	'layout' => [
		'label' => 'Layout',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
				['Default', 'default'],
			]),
		],
	],
	'label_layout' => [
		'label' => 'Label layout',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
				['Default', 'default'],
				['Hidden', 'hidden'],
			]),
		],
	],
	'width' => [
		'label' => 'Width (%)',
		'config' => [
			'type' => 'number',
			'format' => 'integer',
			'default' => 100,
			'size' => 30,
			'range' => [
				'lower' => 20,
				'upper' => 100
			],
			'valuePicker' => [
				'items' => [
					['20', 20],
					['25', 25],
					['33', 33],
					['50', 50],
					['66', 66],
					['75', 75],
					['100', 100],
				],
			],
		],
	],
	'css_class' => [
		'label' => 'CSS Class',
		'config' => [
			'type' => 'input',
			'size' => 30,
		],
	],
	'validation_message' => [
		'label' => 'Custom validation message',
		'config' => [
			'type' => 'input',
			'size' => 30,
		],
	],
	'rte_label' => [
		'label' => 'RTE label',
		'config' => [
			'type' => 'text',
			'rows' => 1,
			'max' => 255,
			'enableRichtext' => true,
			'richtextConfiguration' => 'puck_input_field',
		]
	],
	'disabled' => [
		'label' => 'Disabled',
		'config' => [
			'type' => 'check',
		],
	],
	'readonly' => [
		'label' => 'Readonly',
		'config' => [
			'type' => 'check',
		],
	],
	'multiple' => [
		'label' => 'Multiple',
		'displayCond' => 'FIELD:type:IN:file,email',
		'config' => [
			'type' => 'check',
		],
	],
	'pattern' => [
		'label' => 'RegEx pattern',
		'displayCond' => 'FIELD:type:IN:text,textarea,email,tel,password,url',
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'accept' => [
		'label' => 'Accept',
		'displayCond' => 'FIELD:type:IN:file',
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'maxlength' => [
		'label' => 'Maxlength',
		'displayCond' => 'FIELD:type:IN:text,textarea,email,tel,password,url',
		'config' => [
			'type' => 'number',
			'format' => 'integer',
			'mode' => 'useOrOverridePlaceholder',
			'nullable' => true,
			'default' => null
		],
	],
	'min' => [
		'label' => 'Min',
		'displayCond' => 'FIELD:type:IN:number,range,date',
		'config' => [
			'type' => 'input',
			'eval' => 'is_in',
			'is_in' => '0123456789-.',
			'nullable' => true,
			'default' => null
		],
	],
	'max' => [
		'label' => 'Max',
		'displayCond' => 'FIELD:type:IN:number,range,date',
		'config' => [
			'type' => 'input',
			'eval' => 'is_in',
			'is_in' => '0123456789-.',
			'nullable' => true,
			'default' => null
		],
	],
	'step' => [
		'label' => 'Step',
		'displayCond' => 'FIELD:type:IN:number,range',
		'config' => [
			'type' => 'number',
			'format' => 'decimal',
			'mode' => 'useOrOverridePlaceholder',
			'nullable' => true,
			'default' => null
		],
	],
	'display_condition' => [
		'label' => 'Display condition',
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'autocomplete' => [
		'label' => 'Autocomplete',
		'config' => [
			'type' => 'input',
			'valuePicker' => [
				'items' => [
				],
			],
		],
	],

];
$palettes = [
	'base' => [
		'showitem' => '
		label, identifier, 
		--linebreak--, 
		type, default_value, required, 
		--linebreak--,
		field_options,
		--linebreak--, 
		description, placeholder',
	],
	'config' => [
		'showitem' => '',
	],
	'detail' => [
		'showitem' => '',
	],
	'appearance' => [
		'label' => 'Appearance',
		'showitem' => 'layout, css_class, --linebreak--, width, validation_message, --linebreak--, rte_label',
	],
	'attributes' => [
		'label' => 'Attributes',
		'showitem' => '
		disabled, readonly, multiple, 
		--linebreak--, 
		pattern, accept, maxlength, 
		--linebreak--, 
		min, max, step',
	],
	'rte' => [
		'showitem' => 'type, --linebreak--, label, --linebreak--, description',
	],
	'repeatable-container' => [
		'showitem' => 'type, --linebreak--, label, identifier, --linebreak--, fields',
	]
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        --palette--;;base, 
        --palette--;;detail,
	--div--;Advanced,
        --palette--;;appearance,
       	--palette--;;attributes, 
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
        sys_language_uid, 
        l10n_parent, 
        l10n_diffsource, 
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, 
        hidden';

return [
	'ctrl' => $ctrl,
	'interface' => $interface,
	'columns' => $columns,
	'palettes' => $palettes,
	'types' => [
		'0' => [
			'showitem' => $showItem,
		],
		'number' => [
			'showitem' => $showItem,
			'columnsOverrides' => [
				'default_value' => [
					'config' => [
						'type' => 'input',
						'eval' => 'is_in',
						'is_in' => '0123456789.',
						'default' => null
					],
				],
				'min' => [
					'config' => [
						'type' => 'number',
						'format' => 'decimal',
						'mode' => 'useOrOverridePlaceholder',
						'nullable' => true,
						'default' => null
					],
				],
				'max' => [
					'config' => [
						'type' => 'number',
						'format' => 'decimal',
						'mode' => 'useOrOverridePlaceholder',
						'nullable' => true,
						'default' => null
					],
				],
			],
		],
		'range' => [
			'showitem' => $showItem,
			'columnsOverrides' => [
				'default_value' => [
					'config' => [
						'type' => 'input',
						'eval' => 'is_in',
						'is_in' => '0123456789.',
						'default' => null
					],
				],
				'min' => [
					'config' => [
						'type' => 'number',
						'format' => 'decimal',
						'mode' => 'useOrOverridePlaceholder',
						'nullable' => true,
						'default' => null
					],
				],
				'max' => [
					'config' => [
						'type' => 'number',
						'format' => 'decimal',
						'mode' => 'useOrOverridePlaceholder',
						'nullable' => true,
						'default' => null
					],
				],
			],
		],
		'date' => [
			'showitem' => $showItem,
			'columnsOverrides' => [
				'default_value' => [
					'config' => [
						'type' => 'datetime',
						'format' => 'date',
						'nullable' => true,
						'default' => null
					],
				],
				'min' => [
					'config' => [
						'type' => 'datetime',
						'format' => 'date',
						'nullable' => true,
						'default' => null
					],
				],
				'max' => [
					'config' => [
						'type' => 'datetime',
						'format' => 'date',
						'nullable' => true,
						'default' => null
					],
				],
			],
		],
		'rte' => [
			'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
					--palette--;;rte, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
					sys_language_uid, 
					l10n_parent, 
					l10n_diffsource, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
					hidden',
			'columnsOverrides' => [
				'description' => [
					'config' => [
						'enableRichtext' => true,
					]
				],
			]
		],
		'repeatable-container' => [
			'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
					--palette--;;repeatable-container, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
					sys_language_uid, 
					l10n_parent, 
					l10n_diffsource, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
					hidden',
		]
	],
];
