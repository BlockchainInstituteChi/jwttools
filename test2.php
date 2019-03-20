<?php
	require __DIR__ . '/vendor/autoload.php';
	// use RuntimeException;
	// use Tuupola\Base58;
	require 'vendor/autoload.php';

	use Blockchaininstitute\didResolver;

	$encodedMNID = "2ot1hCuVAL6nQ3NQryjkBARGtsj4rsao575";
	$BCIencodedMNID = "2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ";
	
	$didResolver = new didResolver();
	
	function placeholderCallback ($result) {
		echo $result;
	}

	$didResolver->resolve_did("uPortProfileIPFS1220", $encodedMNID, 'placeHolderCallback');

?>
