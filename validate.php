<?php 

echo "\r\nStarting validate.php \r\n";

require __DIR__ . "/vendor/autoload.php";
use Mdanter\Ecc\Crypto\Signature\SignHasher;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Curves\CurveFactory;
use Mdanter\Ecc\Crypto\Signature\Signer;
use Mdanter\Ecc\Crypto\Signature\Signature;
use Mdanter\Ecc\Crypto\Key\PublicKey;
use Mdanter\Ecc\Curves\SecgCurve;
// use Mdanter\Ecc\Crypto\Curves\CurveFactory;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Serializer\Signature\DerSignatureSerializer;
use Mdanter\Ecc\Serializer\Signature\SimpleEthSerializer;
use Mdanter\Ecc\Serializer\PublicKey\Der\Parser as publicKeyParser;

use kornrunner\Secp256k1;
use kornrunner\Serializer\HexSignatureSerializer;
use kornrunner\Signature\Signature as kSig;
use ionux\Phactor;


$secp256k1 = new Secp256k1();
$CurveFactory = new CurveFactory;


// 2. Import the data 

// $opt = getopt("j:k:");

// the blockchain institute request JWT - the public key is wrong in this one 
$opt2 = [
	"header" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ",
	"jwt" => "eyJpYXQiOjE1NTM3MTM0MDksInJlcXVlc3RlZCI6WyJuYW1lIl0sImNhbGxiYWNrIjoiaHR0cHM6Ly9jaGFzcXVpLnVwb3J0Lm1lL2FwaS92MS90b3BpYy9McWxGWG9QMlA2SFZ1R1dZIiwibmV0IjoiMHg0IiwidHlwZSI6InNoYXJlUmVxIiwiaXNzIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoifQ",
	"publicKey" => "042cf2a7f5d071ab950e68fb6c3be0f06e214517f8ea40fb5ce02636789771452068409b66ab2adb25f8f6fb906559e8917600028356d4d99f61df8668b1236c63",
	"signature" => "9Rr7NTrYqy4cJUhRMgDxELhXs70VFgOlbQoP1mchJFkgHDnowxczbN1eX1BRPDfbyMhMUH-KD5twPgmKc8d-rA"
];

// alex's response confirmation JWT
$opt = [
	"header" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ",
	"jwt" => "eyJpYXQiOjE1NTM4MDE4OTYsImV4cCI6MTU1Mzg4ODI5NiwiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRNNE1ERTRPVEFzSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTlvTURoelRVODBOMjVYY1VzMVYyOVRJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuaURoNWZ4UjZDdEpHV0VBcjg1VzBpd0JXMmhxOTl5UnE2T0ZQbUxpVGxlRmNoclItd3VYcWlGTmI1R203SUQ4VGxsR2RMRGpzSlU4NkV3U0E2dFU2b3ciLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9",
	"publicKey" => "04315249bd0eac98917004d01e71cac4c7954829c0301c97df8e17dbce425c07c0e4a24a96878796e94a4fa8096bb5dc43f0991af0331d4d1dc42c62dae7da4095",
	"signature" => "vFslRV7OGpfCAwQ9HDqr1BoBYNXlzyHjZiJrT4_0exgbrVXTYjbvJ3_6GGtI2yKATxjOUuX5EToNBcTXyPLBUg"
];


// expected values:
// R: 93133403546575067896025509959733302650499112314972800474247511519882782100067
// R' 22737647350238053685553103017513013342407009842522656424897096104546585187
// S: 45294061822258079768881989172036910644512721016363986807841727096112736373809
// S' 10504610962641872662900108605454526111335082211613391818003260852991251152

// 3. Configure data for ECC comparison
$adapter = EccFactory::getAdapter();

$publicKeySerializer = new publicKeyParser($adapter);

$algorithm = 'sha256';

$hasher = new SignHasher($algorithm);
$jwt = '';

$generator = CurveFactory::getGeneratorByName('secp256k1');

$publicKeySerialized = $publicKeySerializer->parseKey($generator, $opt["publicKey"]);

// echo "\r\n\r\n****************\r\n\r\n";

// print_r($publicKeySerialized);

// echo "\r\n\r\n****************\r\n\r\n";


// The document from the JWT entered.  
$document = $opt['header'] . "." . $opt['jwt'];

// echo "\r\n\r\ndocument is:\r\n" , $document , "\r\n";

// $hash = $hasher->makeHash($document, $generator);

$hash = hash('sha256', $document);

// echo "\n\r\nhash is ", $hash, " ", gmp_strval( $hash , 16 ), " \r\n";

$signatureSet = createSignatureObject($opt['signature']);

$signatureK = new kSig ($signatureSet["rGMP"], $signatureSet["sGMP"], $signatureSet["v"]);

// echo "\r\n\r\n serialized sigk:\r\n\r\n " , print_r($signatureK->toHex()), "\r\n\r\n";

// 4. Verify signature using serialized data

$isVerified = $secp256k1->verify($hash, $signatureK, $opt['publicKey']);
// $isVerified = $secp256k1->verifyb($hash, $signatureK, $opt['publicKey']);

// $isVerified = verify( $hash, $signatureK, $publicKeySerialized, $adapter );

// echo "\r\n\r\n****************\r\n\r\n";

echo "isVerified:\r\n" , $isVerified;
// print_r($isVerified);
echo "\r\n\r\n";


function verify( $hash,  $signature,  $publicKey, $adapter ) {

	// echo "\r\n\r\nProcessing verification with hash ";
	// print_r($hash);
	// echo " and signature: \r\n\r\n"; 
	// print_r($signature);
	// echo "\r\n\r\n and pubKey \r\n\r\n"; 
	// print_r($publicKey);
	// dechex($publicKey[""]
	// echo "\r\n\r\n and adapter: \r\n";
	// print_r($adapter);

    // $hex_hash = gmp_init($hash, 16);
    $signer = new Signer( $adapter );

    // NOTE: The verify function doesn't actually use the value passed in for v so it's okay that it's hardcoded

    return $signer->verify($publicKey, $signature, "0x" . $hash);

}

function base64url_decode( $sig ){
	// converts from base64url to base64, then decodes
  	return base64_decode( strtr( $sig, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $sig )) % 4 ));

}
// function base64url_decode($data) {
//   $len = strlen($data);
//   return base64_decode(str_pad(strtr($data, '-_', '+/'), $len + $len % 4, '=', STR_PAD_RIGHT));
// } 

// function base64url_decode( $string ){
//   return base64_decode(urldecode($string));
// }


function createSignatureObject ($signature) {

	$rawSig = base64url_decode($signature);
	// echo "\r\n\r\nrawsig \r\n", $rawSig;
	// echo "\r\n\r\nrawsig hex is \r\n", String2Hex($rawSig);
	
	// $rawSig = String2Hex($t)

	$rBase = substr( $rawSig, 0, 32 );
	$sBase = substr( $rawSig, 32, 64 );
	$rHex = "0x" . String2Hex($rBase);
	$sHex = "0x" . String2Hex($sBase);
	
	// echo " \r\nhex values:\r\n " . $rHex . " \r\n " . $sHex . "\r\n\r\n";

	$sigObj = [
		"hex" => String2Hex($rawSig),
		"r" => String2Hex($rBase),
		"s" => String2Hex($sBase),
		"v" => 0,
		"rGMP" => gmp_init($rHex, 16),
		"sGMP" => gmp_init($sHex, 16)
	];

	// print_r(gmp_strval($sigObj['rGMP'], 16));
	// print_r(gmp_strval($sigObj['sGMP'], 16));

	// if ( "275bebf2ed1d4f95aafb95d43ca8b2518d707f9b0f53c8ffd616a3fba7a658aa" == 
	// 	String2Hex($sBase) ) {
	// 	echo "\r\n\r\n string matched " . String2Hex($sBase) . " 275bebf2ed1d4f95aafb95d43ca8b2518d707f9b0f53c8ffd616a3fba7a658aa";
	// } else {
	// 	echo "\r\n\r\n string not matched " . String2Hex($sBase) . " 275bebf2ed1d4f95aafb95d43ca8b2518d707f9b0f53c8ffd616a3fba7a658aa";
	// }

	// echo "\r\n\r\nsigObj \r\n", var_dump($sigObj);
	return $sigObj;

}

function encodeByteArrayToHex ($byteArray) {

	$chars = array_map("chr", $byteArray);
	$bin = join($chars);
	$hex = bin2hex($bin);

	return $hex;

}

function String2Hex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
    	// echo "\r\nconverting " . $string[$i] . " to " . dechex(ord($string[$i]));

    	$newBit = dechex(ord($string[$i]));

    	if ( strlen($newBit) == 1 ) {
    		$newBit = "0" . $newBit;
    	}

        $hex .= $newBit;
    }
    return $hex;
}


// // Parse signature
// $sigSerializer = new SimpleEthSerializer();

// $sig = $sigSerializer->parse($sigData);

// // Parse public key
// $keyData = file_get_contents(__DIR__ . '/alex-pub.pem');
// // $keyData = $opt["key"];

// $derSerializer = new DerPublicKeySerializer($adapter);

// $pemSerializer = new PemPublicKeySerializer($derSerializer);

// $key = $pemSerializer->parse($keyData);

// $hasher = new SignHasher($algorithm);

// $hash = $hasher->makeHash($document, $generator);

// $signer = new Signer($adapter);

// echo "Signer: ";
// var_dump($signer);

// echo "Sig object just before check: ";
// var_dump($sig);

// $check = $signer->verify($key, $sig, $hash);

// Echo "Signature check: ";
// var_dump($check);

// if ($check) {
//     echo "Signature verified\n";
// } else {
//     echo "Signature validation failed\n";
// }