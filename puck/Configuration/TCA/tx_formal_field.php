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
		'header' => 'form-static-text',
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
				['Repeatable fields', 'repeatable-container', 'default', 'special'],
				['Header', 'header', 'form-static-text', 'no-input'],
				['Rich text content', 'rte', 'form-static-text', 'no-input'],
				['Content element', 'content', 'form-content-element', 'no-input'],
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
//	'server_validators' => [
//		'label' => 'Server-side validators',
//		'config' => [
//			'type' => 'select',
//			'renderType' => 'selectMultipleSideBySide',
//			'default' => 'auto-validators',
//			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
//				['Add validators based on type and attributes', 'auto-validators'],
//				['AlphaNumeric', 'TYPO3\CMS\Extbase\Validation\Validator\AlphaNumericValidator'],
//				['Boolean', 'TYPO3\CMS\Extbase\Validation\Validator\BooleanValidator'],
//				['DateTime', 'TYPO3\CMS\Extbase\Validation\Validator\DateTimeValidator'],
//				['EmailAddress', 'TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator'],
//				['FileName', 'TYPO3\CMS\Extbase\Validation\Validator\FileNameValidator'],
//				['FileSize', 'TYPO3\CMS\Extbase\Validation\Validator\FileSizeValidator'],
//				['Float', 'TYPO3\CMS\Extbase\Validation\Validator\FloatValidator'],
//				['ImageDimensions', 'TYPO3\CMS\Extbase\Validation\Validator\ImageDimensionsValidator'],
//				['Integer', 'TYPO3\CMS\Extbase\Validation\Validator\IntegerValidator'],
//				['MimeType', 'TYPO3\CMS\Extbase\Validation\Validator\MimeTypeValidator'],
//				['NotEmpty', 'TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator'],
//				['NumberRange', 'TYPO3\CMS\Extbase\Validation\Validator\NumberRangeValidator'],
//				['Number', 'TYPO3\CMS\Extbase\Validation\Validator\NumberValidator'],
//				['RegularExpression', 'TYPO3\CMS\Extbase\Validation\Validator\RegularExpressionValidator'],
//				['String', 'TYPO3\CMS\Extbase\Validation\Validator\StringValidator'],
//				['Text', 'TYPO3\CMS\Extbase\Validation\Validator\TextValidator'],
//				['Url', 'TYPO3\CMS\Extbase\Validation\Validator\UrlValidator'],
//			]),
//		],
//	],
//	'server_validators_options' => [
//		'label' => ' Validator options',
//		'config' => [
//			'type' => 'json',
//		],
//	],
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
	'autocomplete' => [
		'label' => 'Autocomplete',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectMultipleSideBySide',
			'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([

				['off', 'off'],
				['on', 'on'],

				['language', 'language', '', 'other'],
				['organization', 'organization', '', 'other'],
				['organization-title', 'organization-title', '', 'other'],
				['photo', 'photo', '', 'other'],
				['sex', 'sex', '', 'other'],
				['transaction-amount', 'transaction-amount', '', 'other'],
				['transaction-currency', 'transaction-currency', '', 'other'],
				['url', 'url', '', 'other'],

				['name', 'name', '', 'name'],
				['family-name', 'family-name', '', 'name'],
				['given-name', 'given-name', '', 'name'],
				['additional-name', 'additional-name', '', 'name'],
				['nickname', 'nickname', '', 'name'],
				['honoric-prefix', 'honoric-prefix', '', 'name'],
				['honoric-suffix', 'honoric-suffix', '', 'name'],
				['username', 'username', '', 'name'],

				['street-address', 'street-address', '', 'address'],
				['postal-code', 'postal-code', '', 'address'],
				['country', 'country', '', 'address'],
				['country-name', 'country-name', '', 'address'],
				['address-level1', 'address-level1', '', 'address'],
				['address-level2', 'address-level2', '', 'address'],
				['address-level3', 'address-level3', '', 'address'],
				['address-level4', 'address-level4', '', 'address'],
				['address-line1', 'address-line1', '', 'address'],
				['address-line2', 'address-line2', '', 'address'],
				['address-line3', 'address-line3', '', 'address'],

				['bday', 'bday', '', 'birthday'],
				['bday-day', 'bday-day', '', 'birthday'],
				['bday-month', 'bday-month', '', 'birthday'],
				['bday-year', 'bday-year', '', 'birthday'],

				['email', 'email', '', 'digital-contact'],
				['tel', 'tel', '', 'digital-contact'],
				['tel-area-code', 'tel-area-code', '', 'digital-contact'],
				['tel-country-code', 'tel-country-code', '', 'digital-contact'],
				['tel-extension', 'tel-extension', '', 'digital-contact'],
				['tel-local', 'tel-local', '', 'digital-contact'],
				['tel-local-prefix', 'tel-local-prefix', '', 'digital-contact'],
				['tel-local-suffix', 'tel-local-suffix', '', 'digital-contact'],
				['tel-national', 'tel-national', '', 'digital-contact'],
				['impp', 'impp', '', 'digital-contact'],

				['cc-name', 'cc-name', '', 'credit-card'],
				['cc-family-name', 'cc-family-name', '', 'credit-card'],
				['cc-given-name', 'cc-given-name', '', 'credit-card'],
				['cc-additional-name', 'cc-additional-name', '', 'credit-card'],
				['cc-csc', 'cc-csc', '', 'credit-card'],
				['cc-exp', 'cc-exp', '', 'credit-card'],
				['cc-exp-month', 'cc-exp-month', '', 'credit-card'],
				['cc-exp-year', 'cc-exp-year', '', 'credit-card'],
				['cc-number', 'cc-number', '', 'credit-card'],
				['cc-type', 'cc-type', '', 'credit-card'],

				['current-password', 'current-password', '', 'password'],
				['new-password', 'new-password', '', 'password'],
				['one-time-code', 'one-time-code', '', 'password'],

				['shipping', 'shipping', '', 'address-group'],
				['billing', 'billing', '', 'address-group'],

				['home', 'home', '', 'contact-type'],
				['work', 'work', '', 'contact-type'],
				['mobile', 'mobile', '', 'contact-type'],
				['fax', 'fax', '', 'contact-type'],
				['page', 'page', '', 'contact-type'],
			]),
			'default' => 'on',
			'itemGroups' => [
				'name' => 'Name',
				'address' => 'Address',
				'birthday' => 'Birthday',
				'digital-contact' => 'Digital contact',
				'credit-card' => 'Credit card',
				'password' => 'Password',
				'address-group' => 'Address group',
				'contact-type' => 'Contact type',
				'other' => 'Other',

			]
		],
	],
	'display_condition' => [
		'label' => 'Display condition',
		'description' => 'Condition in Symfony Expression Language.',
		'config' => [
			'type' => 'input',
			'size' => 100,
			'valuePicker' => [
				'items' => [
					['Field value is true / not empty', 'value("field-id")'],
					['Field value is equal to', 'value("field-id") == "some-value"'],
				],
			],
		],
	],
	'js_display_condition' => [
		'label' => 'Display condition',
		'description' => 'Condition in jexl.',
		'config' => [
			'type' => 'input',
			'size' => 100,
			'valuePicker' => [
				'items' => [
					['Field value is true / not empty', 'value("field-id")'],
					['Field value is equal to', 'value("field-id") == "some-value"'],
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
	],
	'condition' => [
		'showitem' => 'display_condition, --linebreak--, js_display_condition',
	],
	'validation' => [
		'showitem' => 'server_validators, --linebreak--, server_validators_options',
	]
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        --palette--;;base, 
        --palette--;;detail,
	--div--;Advanced,
        --palette--;;appearance,
       	--palette--;;attributes, 
    --div--;Autocomplete,
    	autocomplete,   	
    --div--;Condition,
    	--palette--;;condition, 
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
		'header' => [
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
			]
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
