<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$ctrl = [
    'label' => 'name',
    'title' => 'Author',
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'cruser_id' => 'cruser_id',
    'sortby' => 'sorting',
    'versioningWS' => true,
    'languageField' => 'sys_language_uid',
    'transOrigPointerField' => 'l10n_parent',
    'transOrigDiffSourceField' => 'l10n_diffsource',
    'delete' => 'deleted',
    'iconfile' => 'EXT:puck/Resources/Public/Icons/Backend/Author.svg',
    'enablecolumns' => [
        'disabled' => 'hidden',
    ],
    'searchFields' => 'name',
];
$interface = [
    'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, name, position, description',
];
$columns = [
    'name' => [
        'label' => 'Name',
        'config' => [
            'type' => 'input',
            'size' => 30,
            'eval' => 'trim',
            'required' => true,
        ],
    ],
];
$showItem = 'name';

return [
    'ctrl' => $ctrl,
    'interface' => $interface,
    'columns' => $columns,
    'types' => [
        '0' => [
            'showitem' => $showItem,
        ],
    ],
];
