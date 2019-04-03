<?php
	require __DIR__ . '/vendor/autoload.php';
	// use RuntimeException;
	use Tuupola\Base58;
	use Blockchaininstitute\jwtTools as jwtTools;

	require 'vendor/autoload.php';

	$jwtTools = new jwtTools();
	
	$jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTM4MDE4OTYsImV4cCI6MTU1Mzg4ODI5NiwiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRNNE1ERTRPVEFzSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTlvTURoelRVODBOMjVYY1VzMVYyOVRJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuaURoNWZ4UjZDdEpHV0VBcjg1VzBpd0JXMmhxOTl5UnE2T0ZQbUxpVGxlRmNoclItd3VYcWlGTmI1R203SUQ4VGxsR2RMRGpzSlU4NkV3U0E2dFU2b3ciLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.vFslRV7OGpfCAwQ9HDqr1BoBYNXlzyHjZiJrT4_0exgbrVXTYjbvJ3_6GGtI2yKATxjOUuX5EToNBcTXyPLBUg";

	$encodedMNID = $jwtTools->getSenderMnid($jwt); 
	$BCIencodedMNID = $jwtTools->getAudienceMnid($jwt);

	$infuraPayload = $jwtTools->resolve_did("uPortProfileIPFS1220", $jwt);

	echo "\r\n\r\nInfura Callstring\r\n", $infuraPayload, "\r\n\r\n";

	$params  = new stdClass ();
	$params		->to 	= '0x2cc31912b2b0f3075a87b3640923d45a26cef3ee';
	$params		->data 	= $infuraPayload;

	$payloadOptions = new stdClass();

	$payloadOptions->method 	= 'eth_call';
	$payloadOptions->id 		= 1			;
	$payloadOptions->jsonrpc 	= '2.0'		;
	$payloadOptions->data 		= json_encode(array($params, 'latest'));

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






