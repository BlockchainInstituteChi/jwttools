<?php
	require __DIR__ . '/vendor/autoload.php';
	// use RuntimeException;
	use Tuupola\Base58;
	use Blockchaininstitute\jwtTools as jwtTools;

	require 'vendor/autoload.php';

	$jwtTools = new jwtTools();
	
	$jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTQzMjIxNzcsImV4cCI6MTU1NDQwODU3NywiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRRek1qSXhOallzSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTlVUjB0RmJHRXpZalJYUkZOUlVtOTVJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuaUQxbUdTWGhlWWc4S3JHMVc4czBMaVpxZldvTm5RRDA1SjhFeG9iMlZKazhJdmhRbHRiTDlDZ0E1eGc4UzhPTFZISjhhaVVTNHNBY3Y3YUppUmNMaWciLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.ZKt9hgN44JNvriBocLM9htJ6ZaAUzCrav3LABXm2yf0aE-xmbm5Rk9FuOQdnoRKbj6675U5F5CCqvJHonNl0cA";

	$jwtRequest = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTQzMTgyNTAsInJlcXVlc3RlZCI6WyJuYW1lIl0sImNhbGxiYWNrIjoiaHR0cHM6Ly9jaGFzcXVpLnVwb3J0Lm1lL2FwaS92MS90b3BpYy9GT3ZHd1M4OWxLYmhGTDk1IiwibmV0IjoiMHg0IiwidHlwZSI6InNoYXJlUmVxIiwiaXNzIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoifQ.4Z6usSZP2fFmk1l_yr_ggK4aJmQ_HSheQbzHhfYOqOU-kTf7g8HyOpVwG4Stc056WjHxfHN7ntkfffLtrUBK7g";

	$encodedMNID = $jwtTools->getSenderMnid($jwt); 
	$BCIencodedMNID = $jwtTools->getAudienceMnid($jwt);

	$infuraPayload = $jwtTools->resolve_did("uPortProfileIPFS1220", $jwt);

	echo "\r\n\r\nInfura Callstring\r\n";
	print_r($infuraPayload);
	echo "\r\n\r\n";

	$params  = new stdClass ();
	$params		->to 	= $infuraPayload->rpcUrl;
	$params		->data 	= $infuraPayload->callString;

	$payloadOptions = new stdClass();

	$payloadOptions->method 	= 'eth_call';
	$payloadOptions->id 		= 1			;
	$payloadOptions->jsonrpc 	= '2.0'		;
	$payloadOptions->params 	= array($params, 'latest');

	$payloadOptions = json_encode($payloadOptions);

	print_r( $payloadOptions );

	echo "\r\n\r\n";

	$options = array(CURLOPT_URL => 'https://rinkeby.infura.io/uport-lite-library',
                 CURLOPT_HEADER => true,
                 CURLOPT_FRESH_CONNECT => true,
                 CURLOPT_POSTFIELDS => $payloadOptions
                );

	echo "\r\n\r\nOptions:\r\n";
	print_r($options);
	echo "\r\n\r\n";

	$ch = curl_init();

	curl_setopt_array($ch, $options);

	$response  = curl_exec($ch);
	
	curl_close($ch);


	echo "\r\n\r\n";
	echo "\r\n\r\n";



