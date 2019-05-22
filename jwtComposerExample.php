<?php
	require 'vendor/autoload.php';
	require __DIR__ . '/vendor/autoload.php';

	use Blockchaininstitute\jwtTools as jwtTools;

	$jwtTools = new jwtTools('makeHttpCall');

// Input Data
    $topicName = "Blockchain Institute Login Request";
    // For chasqui, this should be generated from an existing uportJs library for consistancy

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
	// 776e591d9674b1c0fc8182f8574f24734cdeb4dc7ef8c4643d0fda33f4f8e0d6

	$jwtBody->iat 	      = 1556912833;
	$jwtBody->requested   = ['name'];
	$jwtBody->callback    = 'https://chasqui.uport.me/api/v1/topic/1OzSjQRFrF948LLk';
	// $jwtBody->callback 	  = $jwtTools->chasquiFactory($topicName);
	$jwtBody->net      	  = "0x4";
	$jwtBody->type 		  = "shareReq";
	$jwtBody->iss         = '2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ';

	// 2. Create JWT Object
	$jwtBodyJson = json_encode($jwtBody, JSON_UNESCAPED_SLASHES);


	echo "\r\n\r\njsonbody:\r\n";
	print_r($jwtBodyJson);
	echo "\r\n\r\n";

	$jwt = $jwtTools->createJWT($jwtHeaderJson, $jwtBodyJson, $signingKey);
    
    echo "\r\n\r\n======== BEGINNING VERIFICATION =======\r\n\r\n";

	$isVerified = $jwtTools->verifyJWT($jwt);

	echo "\r\n\r\nisVerified:\r\n" , $isVerified;

	echo "\r\n\r\n";

    function spEncodeAndTrim ($payload) {

    	$encoded = base64_encode($payload);
    	if ( sizeof(explode("=", $encoded)) > 1 ) {
	    	$trimmed = explode("=", $encoded)[0];
    	} else {
    		$trimmed = $encoded;
    	}
    	return $trimmed;
    }
    
	function makeHttpCall ($url, $body, $isPost) {

        $options = array(CURLOPT_URL => $url,
                     CURLOPT_HEADER => false,
                     CURLOPT_FRESH_CONNECT => true,
                     CURLOPT_POSTFIELDS => $body,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_POST => $isPost,
                     CURLOPT_HTTPHEADER => array( 'Content-Type: application/json')
                    );

        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
	}