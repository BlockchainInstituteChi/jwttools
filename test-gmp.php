<!-- test-gmp.php -->
<?php 

$signature = "UPl8T05RkzKLZpNkqtd6FValQd4He85ZlQDbb5JC2YiBtWmN0Vv3_8YdIkCKc8SAFlvX0dPkomlbwIU9YvoDeQ";

$rawSig = base64url_decode($signature);

echo "\r\nrawsig length: " . mb_strlen(mb_strtolower($rawSig)) . "\r\n"; 

$firstHalf = String2Hex(substr( $rawSig, 0, 32 ));
$secondHalf = String2Hex(substr( $rawSig, 32, 64 ));

echo "\r\n\r\n1. Splitting: " . $rawSig . "\r\n\r\nfirst:" . $firstHalf . "\r\n\r\nsecond:" . $secondHalf . "\r\n";

$sigObj = [
    "v" => 0,
    "rGMP" => gmp_init("0x" . $firstHalf, 16),
    "sGMP" => gmp_init("0x" . $secondHalf, 16)
];

print_r($sigObj);

print_r(gmp_strval($sigObj["rGMP"], 16));

function String2Hex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        // echo "\r\nconverting " . $string[$i] . " to " . dechex(ord($string[$i]));

        $newBit = dechex(ord($string[$i]));

        if ( strlen($newBit) == 1 ) {
            $newBit = "0" . $newBit;
        }

        $hex .= $newBit;
    }
    return $hex;
}

function base64url_decode( $payload ){

    return base64_decode( strtr( $payload, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $payload )) % 4 ));

}   