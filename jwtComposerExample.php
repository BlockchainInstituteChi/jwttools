<?php
	require __DIR__ . '/vendor/autoload.php';
	// use RuntimeException;
	use Tuupola\Base58;
	use Blockchaininstitute\jwtTools as jwtTools;
	use Mdanter\Ecc\Crypto\Signature\SignHasher;
	use Mdanter\Ecc\EccFactory;
	use Mdanter\Ecc\Curves\CurveFactory;
	use Mdanter\Ecc\Curves\SecgCurve;
	use Mdanter\Ecc\Math\GmpMathInterface;

	use kornrunner\Secp256k1;
	use kornrunner\Signature\Signature as kSig;
	use kornrunner\Signature\Signer;
	use kornrunner\Serializer\HexPrivateKeySerializer;
	use kornrunner\Serializer\HexSignatureSerializer;

	require 'vendor/autoload.php';

	$jwtTools = new jwtTools();

	// Dependancy Integrations
    $secp256k1 = new Secp256k1();
    $CurveFactory = new CurveFactory;
    $adapter = EccFactory::getAdapter();
    $generator = CurveFactory::getGeneratorByName('secp256k1');
    $algorithm = 'sha256';



// Input Data
    $topicName = "Blockchain Institute Login Request";


// Prepare the JWT Header
	// 1. Initialize JWT Values
	$jwtHeader = (object)[];
	$jwtHeader->typ = 'JWT'; // ""
	$jwtHeader->alg = 'ES256K'; // ""

	// 2. Create JWT Object
	$jwtHeaderJson = json_encode($jwtHeader, JSON_UNESCAPED_SLASHES);


// Prepare the JWT Body
	// 1. Initialize JWT Values
	$jwtBody = (object)[];

	 // "Client ID"
	$signingKey  = 'cb89a98b53eec9dc58213e67d04338350e7c15a7f7643468d8081ad2c5ce5480'; // "Private Key"

	$jwtBody->iat 	      = 1556815027;
	$jwtBody->requested   = ['name'];
	$jwtBody->callback    = 'https://chasqui.uport.me/api/v1/topic/QiXBauTEl9RS63Oh';
	// $jwtBody->callback 	  = $jwtTools->chasquiFactory($topicName);
	$jwtBody->net      	  = "0x4";
	$jwtBody->type 		  = "shareReq";
	$jwtBody->iss         = '2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ';
	// $jwtBody->nad         = '2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ';
	// $jwtBody->aud         = '2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ';

	// 2. Create JWT Object
	$jwtBodyJson = json_encode($jwtBody, JSON_UNESCAPED_SLASHES);
	echo "\r\n\r\njsonbody:\r\n";
	print_r($jwtBodyJson);
	echo "\r\n\r\n";


// Encode the components and compose the payload
	$encodedHeader = spEncodeAndTrim($jwtHeaderJson);
    $encodedBody   = spEncodeAndTrim($jwtBodyJson);
    $jwt 		   = $encodedHeader . "." . $encodedBody;

    function spEncodeAndTrim ($payload) {
    	$encoded = strval(base64_encode($payload));
    	echo "\r\n\r\nencoded: \r\n " . $encoded . "\r\n\r\n"; 
    	// $trimmed = substr($encoded, 0, (strlen($payload) - 1/8));
    	if ( sizeof(explode("=", $encoded)) > 1 ) {
	    	$trimmed = explode("=", $encoded)[0];
    	} else {
    		$trimmed = $encoded;
    	}
    	echo "\r\n\r\ntrimmed: \r\n " . $trimmed . "\r\n\r\n";

    	return urlencode($trimmed);
    }

// Create Signature
	// 1. Create a secp256k1 private key 'point' from the hex private key above
	$keySerializer = new HexPrivateKeySerializer($generator);
	$key = $keySerializer->parse($signingKey);

	// 2. Create a hash of the payload body
	$hexHash = hash($algorithm, $jwt);
    $hash = gmp_init($hexHash, 16);

	// 3. Sign the hash 
	$random    = \Mdanter\Ecc\Random\RandomGeneratorFactory::getRandomGenerator();
	// $random = \Mdanter\Ecc\Random\RandomGeneratorFactory::getHmacRandomGenerator($key, $hash, $algorithm); // Alt
    $randomK   = $random->generate($generator->getOrder());
    $signer    = new Signer($adapter);
    $signature = $signer->sign($key, $hash, $randomK);

    $signatureSerializer = new HexSignatureSerializer();
    $hexSignature        = $signatureSerializer->serialize($signature);

	// 4. Return the signed hash, the base 64 encoded header, and the base 64 encoded body
    $jwt.= "." . $hexSignature;

    print_r($jwt);
    