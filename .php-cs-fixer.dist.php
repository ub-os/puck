<?php

declare(strict_types=1);

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->setIndent("\t");
$config->getFinder()
	->in(__DIR__)
	->exclude(['var', 'vendor', 'node_modules', 'Resources', 'Initialisation']);

return $config;
