<?php

	require __DIR__.'/assetmaker.php';
	$maker = new assetMaker();
	echo $maker->make($_GET['name'],$_GET['version']);
	
?>