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

	$jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTQzMDg2ODMsImV4cCI6MTU1NDM5NTA4MywiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRRek1EZzJOamdzSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTlMZURaRFpHWjZjMjl5WjJ0b1lubDBJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuUUFLMFpRTlBiMFI1ZjJCdEVaVU01T0IxcEtSNmlGT1VYWU43NDNMeFhnR1R4SmhUYndsUjFJd1diYk1mcWNWMUZZVkpoZkZvLU5PTlJlWF94cFY0aHciLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.XdTsfgONquHPvtKqXmSWvVPzslSgpdXWVqj3mbRuTTwtK0DcUykCg_dgRB7e9gJRkjZwig5sqTIJnZ7l3igOkQ";

	// Verify signature using serialized data
	$isVerified = $jwtTools->verifyJWT($jwt, $jwtTools->resolvePublicKeyFromJWT($jwt));

	echo "isVerified:\r\n" , $isVerified;

	echo "\r\n\r\n";