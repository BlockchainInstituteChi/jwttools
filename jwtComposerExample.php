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

	// Initialize JWT Values
	$jwtObj = (object)[];
	$jwtObj->mnidAddress = '2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ'; // "Client ID"
	$jwtObj->signingKey  = 'cb89a98b53eec9dc58213e67d04338350e7c15a7f7643468d8081ad2c5ce5480'; // "Private Key"
	$jwtObj->appName     = 'The Blockchain Institute'; 

	// Create JWT Object
	$jwtJson = json_encode($jwtObj);

	// Create Signature
	// 1. Create a secp256k1 private key 'point' from the hex private key above


	// 2. Create a hash of the payload body
	$document = $opt['header'] . "." . $opt['body'];    
    $hash = hash($algorithm, $document);

	// 3. Sign the hash (mdantar?)
	$random = \Mdanter\Ecc\Random\RandomGeneratorFactory::getRandomGenerator();
	// $random = \Mdanter\Ecc\Random\RandomGeneratorFactory::getHmacRandomGenerator($key, $hash, $algorithm); // Alt
    $randomK = $random->generate($generator->getOrder());
    
    $signer = new Signer($adapter);
    $signer->sign($key, $hash, $randomK);


	// 4. Return the signed hash, the base 58 encoded header, and the base 58 encoded body

        

