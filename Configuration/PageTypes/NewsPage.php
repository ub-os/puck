<?php

return new \UBOS\Puck\Configuration\PageTypeConfiguration(
	\UBOS\Puck\Constants::DOKTYPES['news'],
	label: 'News Page',
	icon: 'news_page',
	showItemAdditions: [
		[
			'--div--;Teaser, --palette--;;teaser',
			'after:--palette--;;title'
		],
//		[
//			'link',
//			'after:--palette--;;title'
//		],
		[
			'--palette--;;routing',
			'after:--palette--;;module'
		]
	],
	columnsOverrides: [
		'post_date' => [
			'config' => [
				'required' => 1,
			]
		],
//		'link' => [
//			'config' => [
//				'required' => 0
//			]
//		],
	]
);