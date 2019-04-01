<?php 

echo "\r\nStarting validate.php \r\n";

require __DIR__ . "/vendor/autoload.php";
use Mdanter\Ecc\Crypto\Signature\SignHasher;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Curves\CurveFactory;
use Mdanter\Ecc\Curves\SecgCurve;
use Mdanter\Ecc\Math\GmpMathInterface;

use kornrunner\Secp256k1;
use kornrunner\Signature\Signature as kSig;

$jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTM4MDE4OTYsImV4cCI6MTU1Mzg4ODI5NiwiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRNNE1ERTRPVEFzSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTlvTURoelRVODBOMjVYY1VzMVYyOVRJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuaURoNWZ4UjZDdEpHV0VBcjg1VzBpd0JXMmhxOTl5UnE2T0ZQbUxpVGxlRmNoclItd3VYcWlGTmI1R203SUQ4VGxsR2RMRGpzSlU4NkV3U0E2dFU2b3ciLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.vFslRV7OGpfCAwQ9HDqr1BoBYNXlzyHjZiJrT4_0exgbrVXTYjbvJ3_6GGtI2yKATxjOUuX5EToNBcTXyPLBUg";

$opt = deconstructAndDecode($jwt);
$publicKey = "04315249bd0eac98917004d01e71cac4c7954829c0301c97df8e17dbce425c07c0e4a24a96878796e94a4fa8096bb5dc43f0991af0331d4d1dc42c62dae7da4095";




// 4. Verify signature using serialized data
$isVerified = verifyJWT($jwt, $publicKey);

echo "isVerified:\r\n" , $isVerified;

echo "\r\n\r\n";


function verifyJWT ($jwt, $publicKey) {

	$opt = deconstructAndDecode($jwt);

	$secp256k1 = new Secp256k1();
	$CurveFactory = new CurveFactory;
	$adapter = EccFactory::getAdapter();
	$generator = CurveFactory::getGeneratorByName('secp256k1');

	$signatureSet = createSignatureObject($opt['signature']);	
	$signatureK = new kSig ($signatureSet["rGMP"], $signatureSet["sGMP"], $signatureSet["v"]);

	$algorithm = 'sha256';
	$hasher = new SignHasher($algorithm);

	$document = $opt['header'] . "." . $opt['body'];	
	$hash = hash('sha256', $document);

	return $secp256k1->verify($hash, $signatureK, $publicKey);

}


function deconstructAndDecode ($jwt) {

    $exp = explode(".", $jwt);

    $decodedParts = [
    	"header" => $exp[0],
    	"body" => $exp[1],
    	"signature" => $exp[2]
    ];

    return $decodedParts;

}


function base64url_decode( $payload ){
	// converts from base64url to base64, then decodes
  	return base64_decode( strtr( $payload, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $payload )) % 4 ));

}

function createSignatureObject ($signature) {

	$rawSig = base64url_decode($signature);

	$sigObj = [
		"v" => 0,
		"rGMP" => gmp_init("0x" . String2Hex(substr( $rawSig, 0, 32 )), 16),
		"sGMP" => gmp_init("0x" . String2Hex(substr( $rawSig, 32, 64 )), 16)
	];

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
