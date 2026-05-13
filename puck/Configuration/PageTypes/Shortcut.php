<?php

return new \UBOS\Puck\Configuration\PageTypeConfiguration(
	\UBOS\Puck\Constants::DOKTYPES['shortcut'],
	showItemAdditions: [
		[
			'--palette--;;standard',
			'replace:doktype'
		],
		[
			'--div--;Teaser, --palette--;;teaser',
			'after:--palette--;;shortcutpage'
		]
	],
);