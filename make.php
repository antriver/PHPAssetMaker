<?php

$source = !empty($argv[1]) ? $argv[1] : false;
if (!$source) {
	die('A source is required (or use "all" to make everything)');
}

require __DIR__ . '/lib/AssetMaker.php';

try {

	if (file_exists(__DIR__ . '/assetmaker-settings.json')) {
	
		$settingsFile = __DIR__ . '/assetmaker-settings.json';
		
	} else if (file_exists(dirname(__DIR__) . '/assetmaker-settings.json')) {
	
		$settingsFile = dirname(__DIR__) . '/assetmaker-settings.json');
		
	} else {
	
		die("\nA settings file could not be found in the root or parent directory.");
		
	}

	$maker = new \TMD\AssetMaker(__DIR__ . '/settings.json');

	if ($source == 'all') {
		$maker->makeAll();
	} else {
		$maker->make($source);
	}
} catch (Exception $e) {
	die($e->getMessage() . "\n");
}
