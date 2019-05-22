<?php
	require __DIR__ . '/vendor/autoload.php';

	use Blockchaininstitute\jwtTools as jwtTools;

	echo "\r\nStarting verifyJWT.php \r\n";

	$jwtTools = new jwtTools('makeHttpCall');

	$jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTY5MTI4MzMsInJlcXVlc3RlZCI6WyJuYW1lIl0sImNhbGxiYWNrIjoiaHR0cHM6Ly9jaGFzcXVpLnVwb3J0Lm1lL2FwaS92MS90b3BpYy8xT3pTalFSRnJGOTQ4TExrIiwibmV0IjoiMHg0IiwidHlwZSI6InNoYXJlUmVxIiwiaXNzIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoifQ.eeR7QXHZynWehtl7QsLbFSUgegudarGzuT2YqEUFPRUI3VOJwBVL+2zw0/RDz3kJX7sRdpZwdH0ANKdFz2w4UA";

	$isVerified = $jwtTools->verifyJWT($jwt);

	echo "\r\n\r\nisVerified:\r\n" , $isVerified;

	echo "\r\n\r\n";

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