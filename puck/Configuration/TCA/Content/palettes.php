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
        item_column_width, column_position'
];
$palettes['gridMedia'] = [
    'label' => 'Layout',
    'showitem' => '
        media_layout,
        text_column_width, media_column_width,
        --linebreak--,
        item_column_width, column_position, media_max_height'
];
$palettes['gridCard'] = [
    'label' => 'Layout',
    'showitem' => '
        media_layout, card_media_size, media_column_width'
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
$palettes['bodytext'] = [
    'showitem' => 'bodytext;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext_formlabel',
];
$palettes['menu_pages'] = [
    'showitem' => 'pages; Selected pages, parents; Parent pages',
    'canNotCollapse' => 1
];
$palettes['media'] = [
    'showitem' => 'media',
];

return $palettes;