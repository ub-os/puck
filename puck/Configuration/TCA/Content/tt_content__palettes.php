<?php

$palettes = [];

$palettes['gridContainer'] = [
    'label' => 'Grid container',
    'showitem' => '
        container_width, container_position, container_offset'
];
$palettes['gridContainerWidth'] = [
    'label' => 'Grid container',
    'showitem' => '
        container_width'
];
$palettes['gridColumns'] = [
    'label' => 'Layout',
    'showitem' => '
        item_column_width,flex_grow,
        --linebreak--,
        row_justify, row_align'
];
$palettes['gridColumnsAlignment'] = [
    'label' => 'Layout',
    'showitem' => '
        flex_grow,
        --linebreak--,
        row_justify, row_align'
];
$palettes['gridMedia'] = [
    'label' => 'Layout',
    'showitem' => '
        media_layout,
        text_column_width, media_column_width,
        --linebreak--,
        row_justify, row_align,
        --linebreak--,
        item_column_width, media_max_height'
];
$palettes['gridCard'] = [
    'label' => 'Layout',
    'showitem' => '
        media_layout, card_media_size, media_column_width'
];
$palettes['gridMenuPages'] = [
    'label' => 'Layout',
    'showitem' => '
        item_column_width, row_justify, row_align, 
        --linebreak--,
        media_layout, card_media_size, text_column_width, media_column_width,
        --linebreak--,
        flex_grow'
];
$palettes['appearance'] = [
    'label' => 'Appearance',
    'showitem' => '
        frame_class'
];
$palettes['appearanceLayout'] = [
    'label' => 'Appearance',
    'showitem' => '
        frame_class, layout'
];
$palettes['layout'] = [
    'label' => 'Configuration',
    'showitem' => '
        layout'
];
$palettes['layout_frame_class'] = [
    'label' => 'Configuration',
    'showitem' => '
        frame_class, layout'
];
$palettes['layout_full'] = [
    'label' => 'Configuration',
    'showitem' => '
        frame_class, layout, --linebreak--,
        space_before_class, space_after_class'
];
$palettes['headers'] = [
    'label' => 'Headlines',
    'showitem' => '
        header,
        --linebreak--,
        header_layout, header_position, header_spacing_override, 
        --linebreak--,
        subheader'
];
$palettes['childHeader'] = [
    'label' => 'Headlines',
    'showitem' => '
        header,
        --linebreak--,
        header_layout, header_position'
];
$palettes['bodytext'] = [
    'showitem' => 'bodytext;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext_formlabel',
];
$palettes['menu_pages'] = [
    'label' => 'Menu',
    'showitem' => 'pages; Selected pages, parents; Parent pages',
    'canNotCollapse' => 1
];
$palettes['media'] = [
    'showitem' => 'media',
];
$palettes['menu_files'] = [
    'label' => 'Files',
    'showitem' => '
        assets; Selected files, file_collections,
        --linebreak--,
        filelink_sorting, filelink_sorting_direction, target',
];
foreach($palettes as $name => $palette) {
    $GLOBALS['TCA']['tt_content']['palettes'][$name] = $palette;
}