<?php
	require __DIR__ . '/vendor/autoload.php';
	// use RuntimeException;
	use Tuupola\Base58;
	use Blockchaininstitute\jwtTools as jwtTools;

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

	

// Create Signature
	// 1. Create a secp256k1 private key 'point' from the hex private key above
	$key = $jwtObj->signingKey;

	// 2. Create a hash of the payload body
	$document = $jwt 
    $hash = hash($algorithm, $document);

	// 3. Sign the hash (mdantar?)
	$random = \Mdanter\Ecc\Random\RandomGeneratorFactory::getRandomGenerator();
	// $random = \Mdanter\Ecc\Random\RandomGeneratorFactory::getHmacRandomGenerator($key, $hash, $algorithm); // Alt
    $randomK = $random->generate($generator->getOrder());
    $signer = new Signer($adapter);
    $signer->sign($key, $hash, $randomK);

	// 4. Return the signed hash, the base 64 encoded header, and the base 64 encoded body
    $encodedHeader = base64_encode(urlencode($jwtHeaderJson));
    $encodedBody   = base64_encode(urlencode($jwtBodyJson));
        
    // 