<?php
	require __DIR__ . '/vendor/autoload.php';
	// use RuntimeException;
	use Tuupola\Base58;
	use Blockchaininstitute\jwtTools as jwtTools;

	require 'vendor/autoload.php';

	$jwtTools = new jwtTools();
	
	$jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTQzMjIxNzcsImV4cCI6MTU1NDQwODU3NywiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRRek1qSXhOallzSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTlVUjB0RmJHRXpZalJYUkZOUlVtOTVJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuaUQxbUdTWGhlWWc4S3JHMVc4czBMaVpxZldvTm5RRDA1SjhFeG9iMlZKazhJdmhRbHRiTDlDZ0E1eGc4UzhPTFZISjhhaVVTNHNBY3Y3YUppUmNMaWciLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.ZKt9hgN44JNvriBocLM9htJ6ZaAUzCrav3LABXm2yf0aE-xmbm5Rk9FuOQdnoRKbj6675U5F5CCqvJHonNl0cA";

	$infuraPayload = $jwtTools->resolve_did("uPortProfileIPFS1220", $jwt);

	print_r($infuraPayload);

	$infuraResponse = $jwtTools->resolveInfuraPayload($infuraPayload);

	print_r($infuraResponse);

	$address = json_decode($infuraResponse, true);

	print_r($address);

	echo "\r\n\r\nThe hex string address is " . $address . "\r\n\r\n";
