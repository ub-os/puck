<?php
/***************************************************************
 * Extension Manager/Repository config file for ext: "puck"
 **
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Puck Site Package',
	'description' => '',
	'category' => 'distribution',
	'author' => 'Amadeus Kiener',
	'author_email' => 'a.kiener@unibrand.de',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '1.0.0',
	'constraints' => [
		'depends' => [
            'typo3' => '',
            'content_defender' => ''
		]
	],
);
