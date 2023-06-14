<?php
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use UBOS\Puck\Loader\IconLoader;

IconLoader::loadBackendIcons();

$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerIcon(
    'content-special-shortcut',
    SvgIconProvider::class,
    ['source' => 'EXT:puck/Resources/Public/Icons/Backend/Shortcut.svg']
);

