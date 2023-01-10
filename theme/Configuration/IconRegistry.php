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
    'Auto',
    'None',
    'InlineMedia',
    'ColumnsInlineMedia',
    'AccordionsInlineMedia',
    'CardsInlineMedia',
    'CarouselInlineMedia',
    'CardsCarouselInlineMedia',
    'HeroCarouselInlineMedia',
    'AlignCenter',
    'AlignLeft',
    'AlignRight',
    'AlignSpaceBetween',
    'SizeCover',
    'SizeContain',
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
    'SpaceSmall',
    'SpaceMedium',
    'SpaceLarge',
    'MediaLayoutAbove',
    'MediaLayoutBelow',
    'MediaLayoutLeft',
    'MediaLayoutRight',
    'MediaLayoutLeftFloat',
    'MediaLayoutRightFloat'
];

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