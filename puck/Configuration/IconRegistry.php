<?php
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use UBOS\Puck\Utility\PuckUtility;

$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerIcon(
    'ext-news-wizard-icon',
    SvgIconProvider::class,
    ['source' => 'EXT:news/Resources/Public/icons/Extension.svg']
);
$iconRegistry->registerIcon(
    'content-special-shortcut',
    SvgIconProvider::class,
    ['source' => 'EXT:puck/Resources/Public/Icons/Backend/Shortcut.svg']
);
$iconsPath = ExtensionManagementUtility::extPath('puck') . 'Resources/Public/Icons/Backend/';
$icons = PuckUtility::getBaseFilesInDir($iconsPath, 'svg');
foreach ($icons as $item) {
    $iconRegistry->registerIcon(
        GeneralUtility::camelCaseToLowerCaseUnderscored($item),
        SvgIconProvider::class,
        ['source' => 'EXT:puck/Resources/Public/Icons/Backend/'.$item.'.svg']
    );
}
