<?php
	require __DIR__ . '/vendor/autoload.php';
	// use RuntimeException;
	// use Tuupola\Base58;
	require 'vendor/autoload.php';

	use Blockchaininstitute\did-resolver;

	$didResolver = new didResolver();

	echo did-resolver.resolve_did('test');

?>
