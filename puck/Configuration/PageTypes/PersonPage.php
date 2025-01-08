<?php

return new \UBOS\Puck\Configuration\PageTypeConfiguration(
	16504,
	label: 'Person Page',
	icon: 'person_page',
	showItemAdditions: [
		[
			'page_persons',
			'after:--palette--;;title'
		]
	],
	columnsOverrides: []
);