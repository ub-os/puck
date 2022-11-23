<?php
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;

$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);

$iconRegistry->registerIcon(
    'ext-news-wizard-icon',
    BitmapIconProvider::class,
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
        BitmapIconProvider::class,
        ['source' => 'EXT:theme/Resources/Public/Icons/Content/'.$item.'.svg']
    );
}

/*$iconRegistry->registerIcon(
    'content-special-html',
    BitmapIconProvider::class,
    ['source' => 'EXT:theme/Resources/Public/Icons/Content/Html.svg']
);*/

$iconRegistry->registerIcon(
    'content-special-shortcut',
    BitmapIconProvider::class,
    ['source' => 'EXT:theme/Resources/Public/Icons/Content/Shortcut.svg']
);