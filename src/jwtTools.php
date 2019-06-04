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
use Mdanter\Ecc\Crypto\Signature\Signer;

use kornrunner\Secp256k1;
use kornrunner\Signature\Signature as kSig;
use kornrunner\Serializer\HexPrivateKeySerializer;

use Tuupola\Base58;

class jwtTools
{

    /**
     * Construct
     *
     * @param string $httpCaller (Optional) Passes a string name of a callback function to use 
     *
     * @return string Returns the base 64 encoded and trimmed JWT with a signature generated using the given private key string
     */
    public function __construct($httpCaller)
    {
        if (isset($httpCaller)) {
            $this->httpCaller = $httpCaller;
        } else {
            $this->httpCaller = $this->makeHttpCall;
        }
    }

    /**
     * HttpCaller contains the function which should be used to make http calls
    */
    protected $httpCaller = null;


    /**
     * createJWT
     *
     * @param string $headerJSON Passes a JSON encoded string to act as the header of the JWT
     *
     * @param string $bodyJSON Passes a JSON encoded string to act as the body of the JWT
     *
     * @param string $privateKeyString Passes a string which will be used as the private key to sign the payload
     *
     * @return string Returns the base 64 encoded and trimmed JWT with a signature generated using the given private key string
     */

    public function createJWT ($headerJSON, $bodyJSON, $privateKeyString) {

        $secp256k1 = new Secp256k1();
        $CurveFactory = new CurveFactory;
        $adapter = EccFactory::getAdapter();
        $generator = CurveFactory::getGeneratorByName('secp256k1');

        // Encode the components and compose the payload
        $encodedHeader = $this->spEncodeAndTrim($headerJSON);
        $encodedBody   = $this->spEncodeAndTrim($bodyJSON);
        $jwt           = $encodedHeader . "." . $encodedBody;

        // Create Signature
        // 1. Create a secp256k1 private key 'point' from the hex private key above
        $keySerializer = new HexPrivateKeySerializer($generator);
        $key = $keySerializer->parse($privateKeyString);

        // 2. Create a hash of the payload body
        $hash = hash('sha256', $jwt);
        
        // 3. Sign the hash 
        $signer    = new Signer($adapter);

        $signature = $secp256k1->sign($hash, $privateKeyString, []);

        $hexSignature = $signature->toHex();

        $jwt.= "." . $this->spEncodeAndTrim(hex2bin($hexSignature));

        return $jwt;

    }


    /**
     * verifyJWT
     *
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns either a 1 or 0 to indicate whether the JWT signature was valid
     */

    public function verifyJWT ($jwt) {

        $publicKeyLong = $this->resolvePublicKeyFromJWT($jwt);

        $publicKey =  substr($publicKeyLong, 2);

        $opt = $this->deconstructAndDecode($jwt);

        $u64 = urldecode($opt['signature']);

        $b64 = base64_decode($u64);

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


    /**
     * resolveDIDFromJWT
     *
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns the full DID payload from IPFS in JSON encoded format
     */

    public function resolveDIDFromJWT ($jwt) {
        $infuraPayload = $this->resolve_did("uPortProfileIPFS1220", $jwt);

        $infuraResponse = $this->resolveInfuraPayload($infuraPayload);

        $address = json_decode($infuraResponse, false);

        $addressOutput = $address->result;

        $ipfsEncoded = $this->registryEncodingToIPFS($addressOutput);

        $ipfsResult = json_decode($this->fetchIpfs($ipfsEncoded));
        
        return $ipfsResult;

    }

    /**
     * resolveDIDFromJWT
     *
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns the public key only from the IPFS DID document
     */

    public function resolvePublicKeyFromJWT ($jwt) {

        $ipfsResult = $this->resolveDIDFromJWT($jwt);
        return $ipfsResult->publicKey;

    }

    
    /**
     * resolveDIDFromJWT
     * This is a very mediocre hack that needs to be resolved in the future - function newTopic(topicName) in uport-connect/src/topicFactory.js npm module for expected behaviour
     *
     * @param string $topicName Passes a string to use as a topic name for the chasqui channel
     *
     * @return string Returns a chasqui URL to use as the callback in an auth request
     */

    public function chasquiFactory ($topicName) {
        
        $CHASQUI_URL = 'https://chasqui.uport.me/api/v1/topic/';
        return $CHASQUI_URL;

    }

    /**
     * resolveInfuraPayload
     * 
     * @param string $infuraPayload Passes a JSON encoded request payload to call via HTTP
     *
     * @return object Returns whatever is found after calling infura 
     */

    public function resolveInfuraPayload ($infuraPayload) {
        $params  = (object)[];
        $params     ->to    = $infuraPayload->rpcUrl;
        $params     ->data  = $infuraPayload->callString;

        $payloadOptions = (object)[];

        $payloadOptions->method     = 'eth_call';
        $payloadOptions->id         = 1         ;
        $payloadOptions->jsonrpc    = '2.0'     ;
        $payloadOptions->params     = array($params, 'latest');

        $payloadOptions = json_encode($payloadOptions);

        $result = call_user_func($this->httpCaller, 'https://rinkeby.infura.io/uport-lite-library',  $payloadOptions, 1 );

        return $result;

    }

    /**
     * registryEncodingToIPFS
     * 
     * @param string $hexStr Passes a hex string which needs to be encoded to be part of a infura payload
     *
     * @return string Returns a base 58 encoded string which can be used in infura API Calls 
     */

    public function registryEncodingToIPFS ($hexStr) {
        $base58 = new Base58([
            "characters" => Base58::IPFS,
            "version" => 0x00
        ]);
        $sliced = '1220' . subStr($hexStr, 2);
        $decoded = pack("H*", $sliced);
        $base58enc = $base58->encode($decoded);

        return $base58enc;
    }

    /**
     * registryEncodingToIPFS
     * 
     * @param string $ipfsHash The address of the IPFS record to retrieve
     *
     * @return string Returns the IPFS record associated with that address if applicable 
     */

    public function fetchIpfs($ipfsHash) {
        $uri = "https://ipfs.infura.io/ipfs/" . $ipfsHash;

        $result = call_user_func($this->httpCaller, $uri,  json_encode([]), 0 );

        return $result;
    }

    /**
     * deconstructAndDecode
     * 
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return array Returns JWT components as a three part array 
     */

    public function deconstructAndDecode ($jwt) {

        $exp = explode(".", $jwt);
        $decodedParts = [
            "header" => $exp[0],
            "body" => $exp[1],
            "signature" => $exp[2]
        ];
        return $decodedParts;

    }


    /**
     * resolve_did
     *
     * @param string $profileId Passes the MNID of the sender to be used when composing the registry callstring for Infura
     * 
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns a properly formatted registry call string to be used to retrieve the DID record from Infura 
     */

    public function resolve_did($profileId, $jwt)
    {
        $senderMnid = $this->getSenderMnid($jwt);
        $signerMnid = $this->getAudienceMnid($jwt);

        if ( ( $senderMnid === null ) || ( $signerMnid === null ) ) {
            $signerMnid = $senderMnid = $this->getIssuerMnid($jwt);

            return $this->prepareRegistryCallString($profileId, $senderMnid, $senderMnid);
        } else {
            
            return $this->prepareRegistryCallString($profileId, $senderMnid, $senderMnid);
        }
        
    }

    /**
     * getIssuerMnid
     *
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns either null or the issuer MNID
     */

    public function getIssuerMnid ($jwt) {

        $jsonBody = base64_decode(urldecode(($this->deconstructAndDecode($jwt))["body"]));

        if ( isset((json_decode($jsonBody, true))['iss']) ) {
            $sender = (json_decode($jsonBody, true))['iss'];
            return $sender;
        } else {       
            return null; 
        }

    }

    /**
     * getSenderMnid
     *
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns either null or the sender MNID
     */

    public function getSenderMnid ($jwt) {

        $jsonBody = base64_decode(urldecode(($this->deconstructAndDecode($jwt))["body"]));      

        if ( isset((json_decode($jsonBody, true))['nad']) ) {
            $sender = (json_decode($jsonBody, true))['nad'];
            return $sender;
        } else {       
            return null; 
        }

    }

    /**
     * getAudienceMnid
     *
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns either null or the audience MNID
     */

    public function getAudienceMnid ($jwt) {

        $jsonBody = base64_decode(urldecode(($this->deconstructAndDecode($jwt))["body"]));

        if ( isset(json_decode($jsonBody, true)['aud']) ) {
            $sender = (json_decode($jsonBody, true))['aud'];
            return $sender;

        } else {
            return null;
        }
        
    }    

    /**
     * base64url_decode
     *
     * @param string $payload Passes any string which needs to be encoded
     *
     * @return string Returns a base 64 url encoded string
     */

    public function base64url_decode( $payload ){
        return base64_decode( strtr( $payload, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $payload )) % 4 ));

    }   

    /**
     * encodeByteArrayToHex
     *
     * @param array $byteArray Passes a byte array to be encoded to hex
     *
     * @return string Returns a hex encoded string 
     */

    public function encodeByteArrayToHex ($byteArray) {

        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        $hex = bin2hex($bin);

        return $hex;

    }

    /**
     * String2Hex
     *
     * @param string $string Passes string to convert to hex
     *
     * @return string Returns a hex encoded string 
     */

    public function String2Hex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $newBit = dechex(ord($string[$i]));

            if ( strlen($newBit) == 1 ) {
                $newBit = "0" . $newBit;
            }

            $hex .= $newBit;
        }
        return $hex;
    }

    /**
     * createSignatureObject
     *
     * @param string $signature Passes a string signature from a JWT
     *
     * @return string Returns an array containing GMP encoded r and s values to represent the signature for mathematical use 
     */

    public function createSignatureObject ($signature) {

        $rawSig = $this->base64url_decode($signature);

        $firstHalf = $this->String2Hex(substr( $rawSig, 0, 32 ));
        $secondHalf = $this->String2Hex(substr( $rawSig, 32, 64 ));

        $sigObj = [
            "v" => 0,
            "rGMP" => gmp_init("0x" . $firstHalf, 16),
            "sGMP" => gmp_init("0x" . $secondHalf, 16)
        ];

        return $sigObj;

    }

    /**
     * createSignatureObjectFromHex
     *
     * @param string $signature Passes a string signature from a JWT in hex format
     *
     * @return string Returns an array containing GMP encoded r and s values to represent the signature for mathematical use 
     */

    public function createSignatureObjectFromHex ($signature) {


        $rawSig = $this->base64url_decode($signature);

        $firstHalf  = bin2hex(substr( $rawSig,  0, 32 ));
        $secondHalf = bin2hex(substr( $rawSig, 32, 64 ));

        echo "\r\n\r\n2. Hex Splitting: " . $rawSig . "\r\n\r\nfirst:" . $firstHalf . "\r\n\r\nsecond:" . $secondHalf . "\r\n";

        $sigObj = [
            "v" => 0,
            "rGMP" => gmp_init($firstHalf, 16),
            "sGMP" => gmp_init($secondHalf, 16)
        ];

        return $sigObj;

    }

    /**
     * prepareRegistryCallString
     *
     * @param string $registrationIdentifier Passes a string MNID for the registrar
     *
     * @param string $issuerId Passes a string MNID for the issuer
     *
     * @param string $subjectId Passes a string MNID for the subject (receiver)
     *
     * @return string Returns an object which can be passed to resolveInfuraPayload()
     */

    private function prepareRegistryCallString($registrationIdentifier, $issuerId, $subjectId) {

        $callObj = (object)[];
        $issuer = $this->eaeDecode($issuerId);
        $subject = $this->eaeDecode($subjectId);
        $networks = $this->getNetworks();

        if ( $issuer['network'] !== $subject['network'] ) {
            return "Error: Subject and Issuer must be in the same network!";
        }

        if (!$networks[$issuer['network']]) {
           return 'Network id ' . $issuer['network'] . ' is not configured';
        } 
        
        $callObj->rpcUrl = $networks[$issuer['network']]['registry'];
        $callObj->registryAddress = $networks[$issuer['network']]['registry'];
        $callObj->functionSignature = '0x447885f0';
        $callObj->callString = $this->encodeFunctionCall($callObj->functionSignature, $registrationIdentifier, $issuer['address'], $subject['address']);

        return $callObj;

    }

    /**
     * makeHttpCall
     * This acts as a default HTTP call format for IPFS and Infura calls. An alternative can be supplied in the constructor event for further adaptability.
     *
     * @param string $url Passes the HTTP URL to call
     *
     * @param string $body Passes the payload body. Can be null if GET Request
     *
     * @param string $isPost Passes a boolean to indicate if this is a POST or GET request
     *
     * @return string Returns the result of the call
     */

    public function makeHttpCall ($url, $body, $isPost) {

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

    /**
     * spEncodeAndTrim
     *
     * @param string $payload Passes a value which needs to be encoded to base 64
     *
     * @return string Returns the encoded value after removing any extra '=' characters
     */

    private function spEncodeAndTrim ($payload) {

        $encoded = base64_encode($payload);
        if ( sizeof(explode("=", $encoded)) > 1 ) {
            $trimmed = explode("=", $encoded)[0];
        } else {
            $trimmed = $encoded;
        }
        return $trimmed;
    }

    /**
     * encodeFunctionCall
     *
     * @param string $functionSignature Passes a predefined function signature for the infura call to be made
     *
     * @param string $registrationIdentifier Passes a string MNID for the registrar
     *
     * @param string $issuerId Passes a string MNID for the issuer
     *
     * @param string $subjectId Passes a string MNID for the subject (receiver)
     *
     * @return string Returns the full Infura callstring in the proper format and encoding
     */

    private function encodeFunctionCall ($functionSignature, $registrationIdentifier, $issuer, $subject) {

        $callString = $functionSignature;

        $regStub = $this->String2Hex($registrationIdentifier);
        $issStub = subStr($issuer, (-1)*(strlen($issuer) - 2));
        $subStub = subStr($subject, (-1)*(strlen($issuer) - 2));

        $callString .= $this->pad('0000000000000000000000000000000000000000000000000000000000000000', $regStub, false);
        $callString .= $this->pad('0000000000000000000000000000000000000000000000000000000000000000', $issStub, true);
        $callString .= $this->pad('0000000000000000000000000000000000000000000000000000000000000000', $subStub, true);
        return $callString;

    }

    /**
     * ascii2Hex
     *
     * @param string $string Passes a string to convert from ascii to hex
     *
     * @return string Returns the converted hex value as a string
     */

    private function ascii2Hex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

    /**
     * pad
     *
     * @param string $pad Passes a value to be used to pad the string with
     *
     * @param string $str Passes the string to pad
     *
     * @param string $padLeft Passes a boolean value to indicate whether the padding should be added to the left or right of the string
     *
     * @return string Returns the padded string
     */

    private function pad ($pad, $str, $padLeft) {
        if ( gettype($str) == "undefined" ) {
            return $pad;
        }
        if ( $padLeft === true ) {
            return substr( ($pad . $str), (-1)*strlen($pad) );
        } else {
            return substr( ($str . $pad), 0, strlen($pad) );
        }
    }

    /**
     * eaeDecode
     *
     * @param string $payload Passes payload to decode
     *
     * @return array Returns the decomposed address and network as an array
     */

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

    /**
     * getNetworks
     *
     * @return array Returns the array of available Infura rpcUrls and registries for each network
     */

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