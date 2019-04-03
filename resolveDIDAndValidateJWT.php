<?php
	require __DIR__ . '/vendor/autoload.php';

	// use Mdanter\Ecc\Crypto\Signature\SignHasher;
	// use Mdanter\Ecc\EccFactory;
	// use Mdanter\Ecc\Curves\CurveFactory;
	// use Mdanter\Ecc\Curves\SecgCurve;
	// use Mdanter\Ecc\Math\GmpMathInterface;

	// use kornrunner\Secp256k1;
	// use kornrunner\Signature\Signature as kSig;

	use Blockchaininstitute\jwtTools as jwtTools;

	echo "\r\nStarting validate.php \r\n";

	$jwtTools = new jwtTools();

	$jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTQzMjIxNzcsImV4cCI6MTU1NDQwODU3NywiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRRek1qSXhOallzSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTlVUjB0RmJHRXpZalJYUkZOUlVtOTVJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuaUQxbUdTWGhlWWc4S3JHMVc4czBMaVpxZldvTm5RRDA1SjhFeG9iMlZKazhJdmhRbHRiTDlDZ0E1eGc4UzhPTFZISjhhaVVTNHNBY3Y3YUppUmNMaWciLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.ZKt9hgN44JNvriBocLM9htJ6ZaAUzCrav3LABXm2yf0aE-xmbm5Rk9FuOQdnoRKbj6675U5F5CCqvJHonNl0cA";

	// Replace the line below with an example of the callback functionality using $jwtTools -> resolve_did()
	$publicKey = "04315249bd0eac98917004d01e71cac4c7954829c0301c97df8e17dbce425c07c0e4a24a96878796e94a4fa8096bb5dc43f0991af0331d4d1dc42c62dae7da4095";

	$infuraPayload = $jwtTools->resolve_did("uPortProfileIPFS1220", $jwt);

	

	// Verify signature using serialized data
	$isVerified = $jwtTools->verifyJWT($jwt, $publicKey);

	echo "isVerified:\r\n" , $isVerified;

	echo "\r\n\r\n";