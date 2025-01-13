<?php

return new \UBOS\Puck\Configuration\ContentElementConfiguration(
	'formal_form',
	label: 'Formal form',
	description: 'Plugin to render a formal form.',
	group: 'forms',
	icon: 'menu',
	sorting: 110,
	showItem: '    
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
        --div--;Advanced,
            pi_flexform,',
	columnsOverrides: [
	],
	pluginName: 'FormalForm',
	flexForms: ['pi_flexform' => 'FILE:EXT:puck/Configuration/FlexForms/FormalForm.xml'],
	//previewRenderer: \UBOS\Puck\Preview\PageMenuPreviewRenderer::class,
);