<?php

declare(strict_types=1);

defined('TYPO3') or die();

/**
 * Adds a "Tracking & Security" tab to the site configuration form, giving
 * administrators a place to:
 *
 *  - paste tracking / analytics markup that is injected into <head> and before </body>
 *  - switch the Content-Security-Policy between off / report-only / enforced
 *  - allow inline scripts (relaxes the policy, disables hash mode)
 *  - author additional Content-Security-Policy rules (add / override / remove directives)
 *
 * The values are stored in the site's config.yaml and consumed by:
 *  - \UBOS\Puck\Frontend\PageAssetDecorator (asset + markup injection)
 *  - \UBOS\Puck\Security\Csp\SiteConfigContentSecurityPolicy (mode, inline scripts,
 *    rules, and rule validation on save)
 *
 * The disposition toggle requires Initialisation/Site/puck/csp.yaml to declare
 * both an "enforce:" and a "report:" block.
 */

$GLOBALS['SiteConfiguration']['site']['columns']['puck_tracking_head_html'] = [
	'label' => 'Head markup',
	'description' => 'HTML injected into <head> on every page (e.g. consent manager / GTM loader). '
		. 'Inline <script>/<style> are CSP-hashed automatically.',
	'config' => [
		'type' => 'text',
		'rows' => 8,
		'renderType' => 'codeEditor',
		'format' => 'html',
		'placeholder' => '<script src="https://example.com/gtm.js" async></script>',
	],
];

$GLOBALS['SiteConfiguration']['site']['columns']['puck_tracking_body_html'] = [
	'label' => 'Body end markup',
	'description' => 'Non-script HTML injected before </body> on every page (<noscript>, tracking pixels). '
		. 'An inline <script> pasted here is CSP-hashed and moved to the head.',
	'config' => [
		'type' => 'text',
		'rows' => 8,
		'renderType' => 'codeEditor',
		'format' => 'html',
	],
];

$GLOBALS['SiteConfiguration']['site']['columns']['puck_csp_mode'] = [
	'label' => 'Content-Security-Policy',
	'description' => 'report: send the Content-Security-Policy-Report-Only header (violations logged in the browser '
		. 'console / backend module, nothing blocked). enforce: block violations. off: no CSP header.',
	'config' => [
		'type' => 'select',
		'renderType' => 'selectSingle',
		'default' => 'report',
		'items' => [
			['label' => 'Report only', 'value' => 'report'],
			['label' => 'Enforce', 'value' => 'enforce'],
			['label' => 'Off', 'value' => 'off'],
		],
	],
];

$GLOBALS['SiteConfiguration']['site']['columns']['puck_csp_allow_inline_scripts'] = [
	'label' => 'Allow inline scripts',
	'description' => 'Adds \'unsafe-inline\' to script-src/style-src and disables hash mode. Simpler for sites with '
		. 'many inline snippets, but any inline script then runs. Leave off to keep hash based CSP (recommended).',
	'config' => [
		'type' => 'check',
		'renderType' => 'checkboxToggle',
		'default' => 0,
	],
];

$GLOBALS['SiteConfiguration']['site']['columns']['puck_csp_rules'] = [
	'label' => 'Content-Security-Policy rules',
	'description' => 'One rule per line: "<directive> [mode] <source>...". '
		. 'mode is one of extend (default), append, set, remove, reduce, inherit. '
		. 'Lines starting with # are ignored. Applied on top of the TYPO3 core frontend policy, '
		. 'so \'self\', data: images and YouTube/Vimeo embeds already work. '
		. 'Examples: "script-src https://cdn.example.com" · "frame-src remove https://old.example.com" · '
		. '"default-src set \'self\'".',
	'config' => [
		'type' => 'text',
		'rows' => 12,
	],
];

$GLOBALS['SiteConfiguration']['site']['palettes']['puck_tracking'] = [
	'label' => 'Tracking markup',
	'showitem' => 'puck_tracking_head_html, puck_tracking_body_html',
];

$GLOBALS['SiteConfiguration']['site']['palettes']['puck_csp_behavior'] = [
	'label' => 'Content-Security-Policy',
	'showitem' => 'puck_csp_mode, puck_csp_allow_inline_scripts, --linebreak--, puck_csp_rules',
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ','
	. '--div--;Tracking & Security,'
	. '--palette--;;puck_tracking,'
	. '--palette--;;puck_csp_behavior,';
