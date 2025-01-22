<?php

$ctrl = require __DIR__.'/tx_formal_field/ctrl.php';
$columns = require __DIR__.'/tx_formal_field/columns.php';
$palettes = require __DIR__.'/tx_formal_field/palettes.php';
$types = require __DIR__.'/tx_formal_field/types.php';

return [
	'ctrl' => $ctrl,
	'interface' => [],
	'columns' => $columns,
	'palettes' => $palettes,
	'types' => $types
];
