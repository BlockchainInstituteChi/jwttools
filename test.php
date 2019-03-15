<?php 
	require __DIR__ . '/vendor/autoload.php';
	use RuntimeException;
	use Tuupola\Base58;

	$base58check = new Base58([
	    "characters" => Base58::BITCOIN,
	    "check" => true,
	    "version" => 0x00
	]);

	print $base58check->encode("Hello world!"); /* 19wWTEnNTWna86WmtFsTAr5 */

	try {
	    $base58check->decode("19wWTEnNTWna86WmtFsTArX");
	} catch (RuntimeException $exception) {
	    /* Checksum "84fec52c" does not match the expected "84fec512" */
	    print $exception->getMessage();
	}

?>