<?php 
	require __DIR__ . '/vendor/autoload.php';
	use RuntimeException;
	use Tuupola\Base58;

	$base58 = new Base58([
	    "characters" => Base58::IPFS,
	    "version" => 0x00
	]);

	
	print $base58->decode("2ot1hCuVAL6nQ3NQryjkBARGtsj4rsao575");
	

?>