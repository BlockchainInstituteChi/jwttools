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
	$jwtHeaderJson = json_encode($jwtHeader);


// Prepare the JWT Body
	// 1. Initialize JWT Values
	$jwtBody = (object)[];

	 // "Client ID"
	$signingKey  = 'cb89a98b53eec9dc58213e67d04338350e7c15a7f7643468d8081ad2c5ce5480'; // "Private Key"

	$jwtBody->iat 	      = 1556569499;
	$jwtBody->requested   = ['name'];
	$jwtBody->callback    = 'https://chasqui.uport.me/api/v1/topic/SMD5kwa68CIveHsY';
	// $jwtBody->callback 	  = $jwtTools->chasquiFactory($topicName);
	$jwtBody->net      	  = "0x4";
	$jwtBody->type 		  = "shareReq";
	$jwtBody->iss         = '2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ';


	// 2. Create JWT Object
	$jwtBodyJson = json_encode($jwtBody);

    echo "\r\n";
	print_r($jwtBody);
	print_r($jwtBodyJson);
    echo "\r\n";

// Encode the components and compose the payload
	$encodedHeader = urlencode(base64_encode($jwtHeaderJson));
    $encodedBody   = urlencode(base64_encode($jwtBodyJson));
    $jwt 		   = $encodedHeader . "." . $encodedBody;

    echo "\r\n";
    print_r($jwt);
    echo "\r\n";

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
    