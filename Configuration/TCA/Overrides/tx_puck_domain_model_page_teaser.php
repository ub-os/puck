<?php

declare(strict_types=1);

$mirrorPagesColumn = static function (string $column, string $pagesColumn): void {
	$GLOBALS['TCA']['tx_puck_domain_model_page_teaser']['columns'][$column] = $GLOBALS['TCA']['pages']['columns'][$pagesColumn];
};

$mirrorPagesColumn('title', 'title');
$mirrorPagesColumn('text', 'teaser_text');
$mirrorPagesColumn('media', 'media');
$mirrorPagesColumn('icon', 'icon');
