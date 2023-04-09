<?php

$GLOBALS['TCA']['pages']['palettes']['title']['showitem'] = '
    title,--linebreak--,slug,--linebreak--,nav_title,--linebreak--,subtitle,--linebreak--,teaser_text';

$GLOBALS['TCA']['pages']['palettes']['media']['showitem'] = '
    media,--linebreak--, icon';

$GLOBALS['TCA']['pages']['palettes']['postTitle'] = [
    'label' => $GLOBALS['TCA']['pages']['palettes']['title']['label'],
    'showitem' => 'title,--linebreak--,slug,--linebreak--,nav_title,--linebreak--,subtitle,--linebreak--,post_date,lastUpdated,--linebreak--,teaser_text, post_author'
];
