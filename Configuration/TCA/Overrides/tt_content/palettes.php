<?php

$palettes = [];

$palettes['gridContainer'] = [
	'label' => 'Grid container',
	'showitem' => '
        container_width, 
        --linebreak--,
        container_position, container_offset'
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
		--linebreak--,
        text_column_width, 
		--linebreak--,
        media_column_width,
        item_column_width, 
        --linebreak--,
        row_justify, row_align,
        media_max_height'
];
$palettes['gridCard'] = [
	'label' => 'Layout',
	'showitem' => '
        media_layout, card_media_size,
		--linebreak--,
        media_column_width,
        --linebreak--,
        row_align,'
];
$palettes['gridMenuPages'] = [
	'label' => 'Layout',
	'showitem' => '
        item_column_width, 
        --linebreak--,
        row_justify, row_align, 
        --linebreak--,
        media_layout, card_media_size, 
		--linebreak--,
        text_column_width, media_column_width,
        --linebreak--,
        flex_grow'
];
$palettes['appearanceOptions'] = [
	'label' => 'Appearance',
	'showitem' => '
	header_spacing_override,
	--linebreak--,
	classes',
];
$palettes['appearanceClasses'] = [
	'label' => 'Appearance',
	'showitem' => '
	classes',
];

$palettes['appearance'] = [
//	'label' => 'Appearance',
	'showitem' => '
        frame_class'
];
$palettes['appearanceLayout'] = [
//	'label' => 'Appearance',
	'showitem' => '
        frame_class, layout'
];
$palettes['layout'] = [
	'showitem' => '
        layout'
];
$palettes['headers'] = [
	'label' => 'Headlines',
	'showitem' => '
        header,
        --linebreak--,
        header_layout, header_position,
        --linebreak--,
	 	subheader,'
];
$palettes['header_header_position'] = [
	'label' => 'Headlines',
	'showitem' => '
        header,
        --linebreak--,
        header_position'
];
$palettes['header_subheader_header_position'] = [
	'label' => 'Headlines',
	'showitem' => '
        header,
        --linebreak--,
        subheader,
        --linebreak--,
        header_position'
];
$palettes['bodytext'] = [
	'showitem' => 'bodytext;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext_formlabel',
];
$palettes['page_menu'] = [
	'label' => 'Menu',
	'showitem' => 'pages; Selected pages, parents; Parent pages',
	'canNotCollapse' => 1
];
$palettes['media'] = [
	'showitem' => 'media',
];
$palettes['fileMenu'] = [
	'label' => 'Files',
	'showitem' => '
        media; Selected files, 
        --linebreak--,
        file_collections,
        --linebreak--,
        filelink_sorting, filelink_sorting_direction, target',
];
foreach ($palettes as $name => $palette) {
	$GLOBALS['TCA']['tt_content']['palettes'][$name] = $palette;
}