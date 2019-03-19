<?php
	require __DIR__ . '/vendor/autoload.php';
	// use RuntimeException;
	// use Tuupola\Base58;
	require 'vendor/autoload.php';

	use Blockchaininstitute\didResolver;

	$didResolver = new didResolver();

	echo didResolver.resolve_did('test');

?>
