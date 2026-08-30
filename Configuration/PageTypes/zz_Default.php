<?php

return new \UBOS\Puck\Configuration\PageTypeConfiguration(
	\UBOS\Puck\Constants::DOKTYPES['default'],
	showItemAdditions: [
		[
			'--div--;Teaser, --palette--;;teaser',
			'after:--palette--;;title',
		],
		[
			'--palette--;;routing',
			'after:--palette--;;module',
		],
	],
);
