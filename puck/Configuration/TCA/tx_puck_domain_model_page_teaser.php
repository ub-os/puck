<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$ctrl = [
    'label' => 'title',
    'title' => 'Page teaser',
    'tstamp' => 'tstamp',
    'crdate' => 'crdate',
    'cruser_id' => 'cruser_id',
    'sortby' => 'sorting',
    'versioningWS' => true,
    'languageField' => 'sys_language_uid',
    'transOrigPointerField' => 'l10n_parent',
    'transOrigDiffSourceField' => 'l10n_diffsource',
    'delete' => 'deleted',
    'hideTable' => true,
    'iconfile' => 'EXT:puck/Resources/Public/Icons/Backend/PageTeaser.svg',
    'enablecolumns' => [
        'disabled' => 'hidden',
    ],
    'searchFields' => 'title, text',
    'security' => [
        'ignorePageTypeRestriction' => true,
    ],
    'hideTable' => false
];
$interface = [
    'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, header, subheader, bodytext',
];

$columns = [
    'title' => [
        'label' => 'Title',
        'config' => $GLOBALS['TCA']['pages']['columns']['title']['config']
    ],
    'text' => [
        'label' => 'Text',
        'config' => $GLOBALS['TCA']['pages']['columns']['abstract']['config']
    ],
    'media' => [
        'label' => 'Media',
        'config' => $GLOBALS['TCA']['pages']['columns']['media']['config'],
    ],
    'page' => [
        'label' => 'Page',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
            ],
            'foreign_table' => 'pages',
            'disableNoMatchingValueElement' => true,
            'foreign_table_where' => 'AND (pages.uid = ###REC_FIELD_pid### OR pages.l10n_parent = ###REC_FIELD_pid###) AND pages.sys_language_uid IN (-1, ###REC_FIELD_sys_language_uid###)',
        ],
    ],
    'pid' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
    'parent_table' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
    'sys_language_uid' => [
        'exclude' => true,
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
        'config' => [
            'type' => 'language',
        ],
    ],
    'l10n_parent' => [
        'displayCond' => 'FIELD:sys_language_uid:>:0',
        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => \UBOS\Puckloader\Utility\TcaUtility::selectItemsHelper([
                ['', 0],
            ]),
            'foreign_table' => 'tx_puck_domain_model_page_teaser',
            'foreign_table_where' => 'AND {#tx_puck_domain_model_page_teaser}.{#pid}=###CURRENT_PID### AND {#tx_puck_domain_model_page_teaser}.{#sys_language_uid} IN (-1,0)',
            'default' => 0,
        ],
    ],
    'l10n_diffsource' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],
    'hidden' => $GLOBALS['TCA']['tt_content']['columns']['hidden'],
];

$palettes = [
    'teaser' => [
        'showitem' => '',
    ],
];

$types = [
    '0' => [
        'showitem' => '
                --div--;General, 
                page, title, text, media,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
                    sys_language_uid, 
                    l10n_parent, 
                    l10n_diffsource, 
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, 
                    hidden',
    ],
];

return [
    'ctrl' => $ctrl,
    'interface' => $interface,
    'columns' => $columns,
    'palettes' => $palettes,
    'types' => $types,
];
