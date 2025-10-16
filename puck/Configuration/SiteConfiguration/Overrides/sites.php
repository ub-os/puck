<?php
$GLOBALS['SiteConfiguration']['site']['columns']['route_pageMenu_limitToPages'] = [
	'label' => 'Page Menu pages',
	'description' => 'Comma separated list of page uids where the route enhancer should be active (only necessary if the menu has pagination or category filter enabled)',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectMultipleSideBySide',
		'foreign_table' => 'pages',
		'maxitems' => 99,
		'foreign_table_where' => 'AND {#pages}.{#doktype} NOT IN (3,4,6,7,199,254) AND {#pages}.{#sys_language_uid}=0 AND {#pages}.{#deleted}=0 AND {#pages}.{#hidden}=0',
	]
];
$GLOBALS['SiteConfiguration']['site']['columns']['route_indexedSearch_limitToPages'] = [
	'label' => 'Indexed Search pages',
	'description' => 'Comma separated list of page uids where the route enhancer should be active',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectMultipleSideBySide',
		'foreign_table' => 'pages',
		'maxitems' => 99,
		'foreign_table_where' => 'AND {#pages}.{#doktype} NOT IN (3,4,6,7,199,254) AND {#pages}.{#sys_language_uid}=0 AND {#pages}.{#deleted}=0 AND {#pages}.{#hidden}=0',	]
];
$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = str_replace(
	', routes',
	', routes, --div--;Route Enhancers, route_pageMenu_limitToPages , route_indexedSearch_limitToPages', $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']
);

