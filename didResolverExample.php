<?php
	require __DIR__ . '/vendor/autoload.php';
	// use RuntimeException;
	use Tuupola\Base58;
	use Blockchaininstitute\jwtTools as jwtTools;

	require 'vendor/autoload.php';

	$jwtTools = new jwtTools();
	
	$jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTYyMTQ5MzcsImV4cCI6MTU1NjMwMTMzNywiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRZeU1UUTVNak1zSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTkwTUVsVmNtcEdjVEIzTjNkMlVsWnVJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuWTVtMTFKZmR1UG9hNW1fdm4zYkI4TUlqTHktUWdETHI3YTVMREhJcjgxclBkQWVrcmNKTzJra2UxQmJOOVVaSlVrNUQzZzVCRldqNW81RHM4cWQ0bUEiLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.dhS6KNpA21NJUmxtNmOCBv8ewBIwyOgqak9eXpUKZS8Hk-zpxjbbnkhLaOVHCENFjK2zzm9OxVekgGlwlNoIbw";

	// expected pubkey: 0x04315249bd0eac98917004d01e71cac4c7954829c0301c97df8e17dbce425c07c0e4a24a96878796e94a4fa8096bb5dc43f0991af0331d4d1dc42c62dae7da4095

	// expected callstring: 0x447885f075506f727450726f66696c65495046533132323000000000000000000000000000000000000000000000000045cc630c5a692bb1fc5dcac3a368db549d6cfbf600000000000000000000000045cc630c5a692bb1fc5dcac3a368db549d6cfbf6

	$infuraPayload = $jwtTools->resolve_did("uPortProfileIPFS1220", $jwt);

	echo "\r\n\r\nifp:\r\n\r\n";
	print_r($infuraPayload);

	$infuraResponse = $jwtTools->resolveInfuraPayload($infuraPayload);

	echo "\r\n\r\nifr:\r\n\r\n";
	print_r($infuraResponse);

	$address = json_decode($infuraResponse, false);

	echo "\r\n\r\naddr:\r\n\r\n";

	$addressOutput = $address->result;

	print_r($addressOutput);

	$ipfsEncoded = $jwtTools->registryEncodingToIPFS($addressOutput);

	print_r($ipfsEncoded);

	$ipfsResult = $jwtTools->fetchIpfs($ipfsEncoded);
	
	print_r($ipfsResult);

	// echo "\r\n\r\nThe hex string address is " . $address . "\r\n\r\n";
