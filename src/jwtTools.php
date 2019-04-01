<?php

/*

Copyright (c) 2019 Blockchain Institute

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

namespace Blockchaininstitute;

use Mdanter\Ecc\Crypto\Signature\SignHasher;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Curves\CurveFactory;
use Mdanter\Ecc\Curves\SecgCurve;
use Mdanter\Ecc\Math\GmpMathInterface;

use kornrunner\Secp256k1;
use kornrunner\Signature\Signature as kSig;

use Tuupola\Base58;

class jwtTools
{

    /**
     * Create a new Skeleton Instance
     */
    public function __construct()
    {
    }


    /**
     * Friendly welcome
     *
     * @param string $phrase Phrase to return
     *
     * @return string Returns the phrase passed in
     */

    public function verifyJWT ($jwt, $publicKey) {

        $opt = $this->deconstructAndDecode($jwt);

        $secp256k1 = new Secp256k1();
        $CurveFactory = new CurveFactory;
        $adapter = EccFactory::getAdapter();
        $generator = CurveFactory::getGeneratorByName('secp256k1');

        $signatureSet = $this->createSignatureObject($opt['signature']);   
        $signatureK = new kSig ($signatureSet["rGMP"], $signatureSet["sGMP"], $signatureSet["v"]);

        $algorithm = 'sha256';
        
        $document = $opt['header'] . "." . $opt['body'];    
        $hash = hash($algorithm, $document);

        return $secp256k1->verify($hash, $signatureK, $publicKey);

    }


    public function deconstructAndDecode ($jwt) {

        $exp = explode(".", $jwt);
        $decodedParts = [
            "header" => $exp[0],
            "body" => $exp[1],
            "signature" => $exp[2]
        ];
        return $decodedParts;

    }


    public function resolve_did($profileId, $jwt)
    {

        $mnid = $this->getSenderMnid($jwt);

        $callstring = $this->prepareRegistryCallString($profileId, $mnid, $mnid);

        return $callstring;
    }

    public function getSenderMnid ($jwt) {

        $jsonBody = $this->base64url_decode(($this->deconstructAndDecode($jwt))["body"]);
        $sender = json_decode($jsonBody, false)->nad;
        return $sender;

    }

    public function getAudienceMnid ($jwt) {

        $jsonBody = $this->base64url_decode(($this->deconstructAndDecode($jwt))["body"]);
        $sender = json_decode($jsonBody, false)->aud;
        return $sender;

    }    

    // Utilities Functionality
    public function base64url_decode( $payload ){
        // converts from base64url to base64, then decodes
        return base64_decode( strtr( $payload, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $payload )) % 4 ));

    }    

    public function encodeByteArrayToHex ($byteArray) {

        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        $hex = bin2hex($bin);

        return $hex;

    }

    public function String2Hex($string){
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

    private function createSignatureObject ($signature) {

        $rawSig = $this->base64url_decode($signature);

        $sigObj = [
            "v" => 0,
            "rGMP" => gmp_init("0x" . $this->String2Hex(substr( $rawSig, 0, 32 )), 16),
            "sGMP" => gmp_init("0x" . $this->String2Hex(substr( $rawSig, 32, 64 )), 16)
        ];

        return $sigObj;

    }

    private function prepareRegistryCallString($registrationIdentifier, $issuerId, $subjectId) {

        $issuer = $this->eaeDecode($issuerId);
        $subject = $this->eaeDecode($subjectId);

        $networks = $this->getNetworks();

        if ( $issuer['network'] !== $subject['network'] ) {
            return "Error: Subject and Issuer must be in the same network!";
        }

        if (!$networks[$issuer['network']]) {
           return 'Network id ' . $issuer['network'] . ' is not configured';
        } 
        
        $rpcUrl = $networks[$issuer['network']]['registry'];
        $registryAddress = $networks[$issuer['network']]['registry'];

        $functionSignature = '0x447885f0';

        $callString = $this->encodeFunctionCall($functionSignature, $registrationIdentifier, $issuer['address'], $subject['address']);

        return $callString;

    }

    private function encodeFunctionCall ($functionSignature, $registrationIdentifier, $issuer, $subject) {
        $callString = $functionSignature;

        $regStub = $this->String2Hex($registrationIdentifier);
        $issStub = subStr($issuer, (-1)*(sizeof($issuer) - 3));
        $subStub = subStr($subject, (-1)*(sizeof($issuer) - 3));

        $callString .= $this->pad('0000000000000000000000000000000000000000000000000000000000000000', $regStub, false);
        $callString .= $this->pad('0000000000000000000000000000000000000000000000000000000000000000', $issStub, true);
        $callString .= $this->pad('0000000000000000000000000000000000000000000000000000000000000000', $subStub, true);
        
        return $callString;

    }

    private function pad ($pad, $str, $padLeft) {
        if ( gettype($str) == "undefined" ) {
            return $pad;
        }
        if ( $padLeft ) {
            return substr( ($pad . $str), (-1)*strlen($pad) );
        } else {
            return substr( ($str . $pad), 0, (-1)*strlen($pad) );
        }
    }

    private function eaeDecode ($payload) {
        $base58 = new Base58([
            "characters" => Base58::IPFS,
            "version" => 0x00
        ]);
        $data = unpack( "C*", $base58->decode($payload) );
        $netLength = sizeof($data) - 24;
        $network = array_slice($data, 1, $netLength - 1);
        $address = array_slice($data, $netLength, 20 + $netLength - 2);
        $network = "0x" . $this->encodeByteArrayToHex($network);
        $address = "0x" . $this->encodeByteArrayToHex($address);
        return [
            "address" => $address,
            "network" => $network
        ];              
    }

    private function getNetworks () {
        return [
              '0x01' => [
                    'registry' => '0xab5c8051b9a1df1aab0149f8b0630848b7ecabf6',
                    'rpcUrl' => 'https://mainnet.infura.io'
              ], 
              '0x02' => [
                    'registry' => '0x41566e3a081f5032bdcad470adb797635ddfe1f0',
                    'rpcUrl' => 'https://ropsten.infura.io'
              ], 
              '0x03' => [
                    'registry' => '0x5f8e9351dc2d238fb878b6ae43aa740d62fc9758',
                    'rpcUrl' => 'https://kovan.infura.io'
              ],
              '0x04' => [
                    'registry' => '0x2cc31912b2b0f3075a87b3640923d45a26cef3ee',
                    'rpcUrl' => 'https://rinkeby.infura.io'
              ]
        ];
    }

}

?>