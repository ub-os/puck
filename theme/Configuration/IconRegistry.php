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
    'InlineMedia',
    'ColumnsInlineMedia',
    'AccordionsInlineMedia',
    'CardsInlineMedia',
    'CarouselInlineMedia',
    'CardsCarouselInlineMedia',
    'StageCarouselInlineMedia',
    'Center',
    'Left',
    'Right',
    'Cover',
    'Contain',
    'Space-between',
    'ColumnWidth2',
    'ColumnWidth3',
    'ColumnWidth4',
    'ColumnWidth5',
    'ColumnWidth6',
    'ColumnWidth7',
    'ColumnWidth8',
    'ColumnWidth9',
    'ColumnWidth10',
    'ColumnWidth11',
    'ColumnWidth12',
    'Space_auto',
    'Space_none',
    'Space_small',
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
        strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $item)),
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