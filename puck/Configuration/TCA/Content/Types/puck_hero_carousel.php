<?php
use UBOS\Puck\Utility\TcaUtility;

/**
 * puck_anchor
 */
return [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;layout,
            --palette--;;headers,'
        .TcaUtility::getContentShowitemBase(),
    'columnsOverrides' => [
    ]
];