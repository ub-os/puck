<?php


$GLOBALS['TCA']['pages']['palettes']['standard']['showitem'] = '
        doktype, post_date,';

$GLOBALS['TCA']['pages']['palettes']['author'] = [
	'label' => 'Author',
	'showitem' => 'post_author'
];
$GLOBALS['TCA']['pages']['palettes']['teaser'] = [
	'label' => 'Teaser',
	'showitem' => '
        teaser_text,
        --linebreak--,
        teaser_cta,
        --linebreak--,
        media',
];
$GLOBALS['TCA']['pages']['palettes']['media']['showitem'] = '';

$GLOBALS['TCA']['pages']['palettes']['title'] = [
	'label' => 'Title',
	'showitem' => '
        title,
        --linebreak--,
        slug,
        --linebreak--,
        nav_title,
        --linebreak--,
        breadcrumb_title,
        --linebreak--,
        subtitle,
        --linebreak--,
        icon
    ',
];
$GLOBALS['TCA']['pages']['palettes']['teaser'] = [
	'label' => 'Teaser',
	'showitem' => '
        teaser_title,
        --linebreak--,
        teaser_text,
        --linebreak--,
        media,
        --linebreak--,
        teasers
    ',
];

$GLOBALS['TCA']['pages']['palettes']['routing'] = [
	'label' => 'Routing',
	'showitem' => '
        route_enhancers,
    ',
];

