<?php

$source = !empty($argv[1]) ? $argv[1] : false;
if (!$source) {
	die('A source is required (or use "all" to make everything)');
}

require __DIR__ . '/lib/AssetMaker.php';

try {
	$maker = new \TMD\AssetMaker(__DIR__ . '/settings.json');

	if ($source == 'all') {
		$maker->makeAll();
	} else {
		$maker->make($source);
	}
} catch (Exception $e) {
	die($e->getMessage() . "\n");
}
