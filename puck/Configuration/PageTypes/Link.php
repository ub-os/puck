<?php

return new \UBOS\Puck\Configuration\PageTypeConfiguration(
	\UBOS\Puck\Constants::DOKTYPES['link'],
	showItemAdditions: [
		[
			'--palette--;;standard',
			'replace:doktype'
		],
		[
			'--div--;Teaser, --palette--;;teaser',
			'after:--palette--;;external'
		]
	],
);