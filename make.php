<?php

	$source = $argv[1];
	if ( !$source )
	{
		die('A source is required (or use "all" to make everything)');
	}
	
	require __DIR__.'/lib/assetmaker.php';
	
	try
	{
		$maker = new assetMaker( __DIR__.'/settings.json' );
		
		if ( $source == 'all' )
		{
			$maker->makeAll();
		}
		else
		{
			$maker->make( $source );
		}
	}
	catch(Exception $e)
	{
		die($e->getMessage()."\n");
	}

?>