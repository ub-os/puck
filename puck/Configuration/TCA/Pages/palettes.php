<?php


$GLOBALS['TCA']['pages']['palettes']['meta'] = [
    'label' => 'Meta',
    'showitem' => '
        post_date
    ',
];
$GLOBALS['TCA']['pages']['palettes']['postMeta'] = [
    'label' => 'Meta',
    'showitem' => '
        post_date, post_author
    ',
];
$GLOBALS['TCA']['pages']['palettes']['teaser'] = [
    'label' => 'Teaser',
    'showitem' => '
        teaser_text,
        --linebreak--,
        teaser_cta',
];
$GLOBALS['TCA']['pages']['palettes']['media']['showitem'] = '
    media,--linebreak--, icon';
