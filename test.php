<?php 

require __DIR__ . '/vendor/autoload.php';
use RuntimeException;
use Tuupola\Base58;



$encodedMNID = "2ot1hCuVAL6nQ3NQryjkBARGtsj4rsao575";
$BCIencodedMNID = "2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ";




eaeDecode($encodedMNID);



function eaeDecode ($payload) {
	$base58 = new Base58([
	    "characters" => Base58::IPFS,
	    "version" => 0x00
	]);

	echo "\r\n** eaeDecode ran with encoded payload ** \r\n", $payload, "\r\ntype: \r\n", gettype($payload), "\r\n";
	$data = unpack( "C*", $base58->decode($payload) );
	echo "\r\ndecoded data was \r\n", $data, 
			"\r\ntype: \r\n", gettype($data), 
			"\r\n length: ", sizeof($data), 
			"\r\n first: ", $data[1], 
			"\r\n second: ", $data[2],
			"\r\n third: ", $data[3],
			"\r\n fourth: ", $data[4],
			"\r\n";
	$netLength = sizeof($data) - 24;
	echo "\r\nnetlength is \r\n", $netLength, 
			"\r\ntype: \r\n", gettype($netLength), "\r\n";
	$network = array_slice($data, 1, $netLength - 1);
	echo "\r\nnetwork is \r\n", var_dump($network), 
			"\r\ntype: \r\n", gettype($network), "\r\n";
	$address = array_slice($data, $netLength, 20 + $netLength - 2);
	echo "\r\naddress is \r\n", var_dump($address), 
			"\r\ntype: \r\n", gettype($address), "\r\n";

	$network = "0x" . encodeByteArrayToHex($network);
	$address = "0x" . encodeByteArrayToHex($address);

	echo "\r\nnetwork: ", $network, 
			"\r\naddress: ", $address,
			"\r\n";

				
}

function encodeByteArrayToHex ($byteArray) {

	$chars = array_map("chr", $byteArray);
	$bin = join($chars);
	$hex = bin2hex($bin);

	return $hex;

}
	
function eaeDecodeHex ($payload) {
	$base58 = new Base58([
	    "characters" => Base58::IPFS,
	    "version" => 0x00
	]);

	echo "\r\n** eaeDecode ran with encoded payload ** \r\n", $payload, "\r\ntype: \r\n", gettype($payload), "\r\n";
	$data = unpack( "H*", $base58->decode($payload) )[1];
	echo "\r\ndecoded data was \r\n", $data, 
			"\r\ntype: \r\n", gettype($data), 
			"\r\n length: ", strlen($data), 
			"\r\n first: ", $data[0], 
			"\r\n second: ", $data[1],
			"\r\n third: ", $data[2],
			"\r\n fourth: ", $data[3],
			"\r\n";
	$netLength = strlen($data) - 24;
	echo "\r\nnetlength is \r\n", $netLength, 
			"\r\ntype: \r\n", gettype($netLength), "\r\n";
	$network = substr(1, $netLength);
	echo "\r\nnetwork is \r\n", $network, 
			"\r\ntype: \r\n", gettype($network), "\r\n";
	$address = substr($netLength, 20 + $netLength);
	echo "\r\naddress is \r\n", $address, 
			"\r\ntype: \r\n", gettype($address), "\r\n";
}
	
?>