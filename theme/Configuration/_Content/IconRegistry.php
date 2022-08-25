<?php
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('theme') .'/Configuration/_Content/_CTypes/CTypesIconRegistry.php';
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

$iconRegistry->registerIcon(
    'ext-news-wizard-icon',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:news/Resources/Public/icons/Extension.svg']
);

$icons = [
    'Space_auto',
    'Space_none','Space_small',
    'Space_medium',
    'Space_large',
    'ImageOrient_left-float',
    'ImageOrient_right-float',
    'ImageOrient_left-top',
    'ImageOrient_right-top',
    'ImageOrient_top-center',
    'ImageOrient_bottom-center',
    'ImageOrient_right-cover-in-card',
    'ImageOrient_left-cover-in-card',
    'ImageOrient_above-in-card',
    'ImageOrient_below-in-card',
    'ImageOrient_above-full-in-card',
    'ImageOrient_below-full-in-card'];

foreach ($icons as $item) {
    $iconRegistry->registerIcon(
        strtolower($item),
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        ['source' => 'EXT:theme/Resources/Public/images/ctype-icons/default/'.$item.'.svg']
    );
}

/*$iconRegistry->registerIcon(
    'content-special-html',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme/Resources/Public/images/ctype-icons/default/Html.svg']
);
$iconRegistry->registerIcon(
    'content-special-shortcut',
    \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
    ['source' => 'EXT:theme/Resources/Public/images/ctype-icons/default/Shortcut.svg']
);*/