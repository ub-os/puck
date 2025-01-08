<?php
$GLOBALS['SiteConfiguration']['site']['columns']['route_pageMenu_limitToPages'] = [
	'label' => 'Page Menu pages',
	'description' => 'Comma separated list of page uids where the route enhancer should be active',
	'config' => [
		'type' => 'input',
	]
];
$GLOBALS['SiteConfiguration']['site']['columns']['route_indexedSearch_limitToPages'] = [
	'label' => 'Indexed Search pages',
	'description' => 'Comma separated list of page uids where the route enhancer should be active',
	'config' => [
		'type' => 'input',
	]
];
$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] = str_replace(
	', routes',
	', routes, --div--;Route Enhancers, route_pageMenu_limitToPages , route_indexedSearch_limitToPages', $GLOBALS['SiteConfiguration']['site']['types']['0']['showitem']
);

