<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use UBOS\Puckloader\Utility\PuckloaderUtility;

return array_merge(
    PuckloaderUtility::getIconConfigurationFromPath('Resources/Public/Icons/Backend/', 'puck'),
    [
        'content-special-shortcut' => [
            'provider' => SvgIconProvider::class,
            'source' => 'EXT:puck/Resources/Public/Icons/Backend/Shortcut.svg'
        ]
    ]
);