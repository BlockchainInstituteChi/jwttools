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


// Prepare the JWT Header
	// 1. Initialize JWT Values
	$jwtHeader = (object)[];
	$jwtHeader->field = 'value'; // ""

	// 2. Create JWT Object
	$jwtHeaderJson = json_encode($jwtHeader);


// Prepare the JWT Body
	// 1. Initialize JWT Values
	$jwtBody = (object)[];
	$jwtBody->mnidAddress = '2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ'; // "Client ID"
	$jwtBody->signingKey  = 'cb89a98b53eec9dc58213e67d04338350e7c15a7f7643468d8081ad2c5ce5480'; // "Private Key"
	$jwtBody->appName     = 'The Blockchain Institute'; 

	// 2. Create JWT Object
	$jwtBodyJson = json_encode($jwtBody);


// Encode the components and compose the payload
	$encodedHeader = base64_encode(urlencode($jwtHeaderJson));
    $encodedBody   = base64_encode(urlencode($jwtBodyJson));
    $jwt 		   = $encodedHeader . "." . $encodedBody;


// Create Signature
	// 1. Create a secp256k1 private key 'point' from the hex private key above
	$keySerializer = new HexPrivateKeySerializer($generator);
	$key = $keySerializer->parse($jwtBody->signingKey);

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
    