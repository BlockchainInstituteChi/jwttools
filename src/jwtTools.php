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
     * @param string $http_caller (Optional) Passes a string name of a callback function to use 
     *
     * @return string Returns the base 64 encoded and trimmed JWT with a signature generated using the given private key string
     */
    public function __construct( $http_caller ){

        $this->http_caller = 'make_http_call';

        // if (isset($http_caller)) {
        //     $this->http_caller = $http_caller;
        // } else {
        //     $this->http_caller = 'make_http_call';
        // }
    }

    /**
     * http_caller contains the function which should be used to make http calls
    */
    protected $http_caller = null;

    /**
     * create_JWT
     *
     * @param string $headerJSON Passes a JSON encoded string to act as the header of the JWT
     *
     * @param string $bodyJSON Passes a JSON encoded string to act as the body of the JWT
     *
     * @param string $privateKeyString Passes a string which will be used as the private key to sign the payload
     *
     * @return string Returns the base 64 encoded and trimmed JWT with a signature generated using the given private key string
     */

    public function create_JWT( $headerJSON, $bodyJSON, $privateKeyString ) {

        $secp256k1 = new Secp256k1();
        $CurveFactory = new CurveFactory;
        $adapter = EccFactory::getAdapter();
        $generator = CurveFactory::getGeneratorByName( 'secp256k1' );

        $jwt           = $this->sp_encode_and_trim( $headerJSON ) . "." . $this->sp_encode_and_trim( $bodyJSON );
        
        $keySerializer = new HexPrivateKeySerializer( $generator );

        $signature = $secp256k1->sign( hash( 'sha256', $jwt ), $privateKeyString, [] );

        return $jwt . "." . $this->sp_encode_and_trim( hex2bin( $signature->toHex() ) );

    }


    /**
     * verify_JWT
     *
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns either a 1 or 0 to indicate whether the JWT signature was valid
     */

    public function verify_JWT( $jwt ) {

        $ipfsResult = $this->resolve_DID_from_JWT( $jwt );

        $public_key =  substr( $ipfsResult->publicKey, 2 );

        $opt = $this->deconstruct_and_decode( $jwt );

        $secp256k1 = new Secp256k1();
        $CurveFactory = new CurveFactory;
        $adapter = EccFactory::getAdapter();
        $generator = CurveFactory::getGeneratorByName( 'secp256k1' );
        
        $signatureSet = $this->create_signature_object( $opt['signature'] ); 

        $signatureK = new kSig ( $signatureSet['rGMP'], $signatureSet['sGMP'], $signatureSet['v']);

        $hash = hash( 'sha256', $opt[ 'header' ] . '.' . $opt[ 'body' ] );

        return $secp256k1->verify( $hash, $signatureK, $public_key );

    }


    /**
     * resolve_DID_from_JWT
     *
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns the full DID payload from IPFS in JSON encoded format
     */

    public function resolve_DID_from_JWT( $jwt ) {
        $infura_payload = $this->resolve_did( "uPortProfileIPFS1220", $jwt );

        $ipfs_record = json_decode( $this->resolve_infura_payload( $infura_payload ), false );

        $ipfs_encoded = $this->registry_encoding_to_IPFS( $ipfs_record->result );

        return json_decode( $this->fetch_ipfs( $ipfs_encoded ) );

    }

    /**
     * resolve_infura_payload
     * 
     * @param string $infura_payload Passes a JSON encoded request payload to call via HTTP
     *
     * @return object Returns whatever is found after calling infura 
     */

    public function resolve_infura_payload( $infura_payload ) {
        $params  = ( object ) [];
        $params  ->to    = $infura_payload->rpcUrl;
        $params  ->data  = $infura_payload->callString;

        $payload_options = ( object ) [];

        $payload_options->method     = 'eth_call';
        $payload_options->id         = 1         ;
        $payload_options->jsonrpc    = '2.0'     ;
        $payload_options->params     = array($params, 'latest');

        $payload_options = json_encode( $payload_options );

        return $this->make_http_call( 'https://rinkeby.infura.io/uport-lite-library',  $payload_options, 1 );

    }

    /**
     * registry_encoding_to_IPFS
     * 
     * @param string $hexStr Passes a hex string which needs to be encoded to be part of a infura payload
     *
     * @return string Returns a base 58 encoded string which can be used in infura API Calls 
     */

    public function registry_encoding_to_IPFS( $hexStr ) {
        $base58 = new Base58( [
            "characters" => Base58::IPFS,
            "version"    => 0x00
        ] );
        $sliced = '1220' . subStr( $hexStr, 2 );
        $decoded = pack( "H*", $sliced );
        return  $base58->encode( $decoded );

    }

    /**
     * registry_encoding_to_IPFS
     * 
     * @param string $ipfsHash The address of the IPFS record to retrieve
     *
     * @return string Returns the IPFS record associated with that address if applicable 
     */

    public function fetch_ipfs( $ipfsHash ) {

        $uri = "https://ipfs.infura.io/ipfs/" . $ipfsHash;

        return $this->make_http_call( $uri,  json_encode( [] ), 0 );

    }

    /**
     * deconstruct_and_decode
     * 
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return array Returns JWT components as a three part array 
     */

    public function deconstruct_and_decode( $jwt ) {

        $exp = explode( ".", $jwt );
        return [
            "header"    => $exp[ 0 ],
            "body"      => $exp[ 1 ],
            "signature" => $exp[ 2 ]
        ];

    }


    /**
     * resolve_did
     *
     * @param string $profile_id Passes the MNID of the sender to be used when composing the registry callstring for Infura
     * 
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @return string Returns a properly formatted registry call string to be used to retrieve the DID record from Infura 
     */

    public function resolve_did( $profile_id, $jwt )
    {
        $sender_mnid = $this->get_mnid( $jwt, 'nad' );
        $signer_mnid = $this->get_mnid( $jwt, 'aud' );

        if ( ( $sender_mnid === null ) || ( $signer_mnid === null ) ) {
        
            $signer_mnid = $sender_mnid = $this->get_mnid( $jwt, 'iss' );
            return $this->prepare_registry_callstring( $profile_id, $sender_mnid, $sender_mnid );

        } else {

            return $this->prepare_registry_callstring( $profile_id, $sender_mnid, $sender_mnid );
        
        }
        
    }

    /**
     * get_mnid
     *
     * @param string $jwt Passes a properly formed JWT String containing a base 64 url encoded header and body and a valid signature element
     *
     * @param string $mode Passes either iss, nad, or aud 
     *     
     * @return string Returns either null or the issuer MNID
     */

    public function get_mnid( $jwt, $mode ) {

        $jsonBody = base64_decode( urldecode( ( $this->deconstruct_and_decode( $jwt ) )[ "body" ] ));
        if ( isset( ( json_decode( $jsonBody, true ) )[ $mode ] ) ) {
            $sender = ( json_decode( $jsonBody, true ) )[ $mode ];
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
        return base64_decode( strtr( $payload, '-_', '+/' ) . str_repeat( '=', 3 - ( 3 + strlen( $payload ) ) % 4 ) );

    }   

    /**
     * encode_byte_array_to_hex
     *
     * @param array $byte_array Passes a byte array to be encoded to hex
     *
     * @return string Returns a hex encoded string 
     */

    public function encode_byte_array_to_hex( $byte_array ) {

        $chars = array_map( "chr", $byte_array );
        $bin = join( $chars );
        return bin2hex( $bin );

    }

    /**
     * string_to_hex
     *
     * @param string $string Passes string to convert to hex
     *
     * @return string Returns a hex encoded string 
     */

    public function string_to_hex( $string ){
        $hex='';
        for ( $i=0; $i < strlen( $string ); $i++ ){
            $newBit = dechex( ord( $string[ $i ] ) );

            if ( strlen( $newBit ) == 1 ) {
                $newBit = "0" . $newBit;
            }

            $hex .= $newBit;
        }
        return $hex;
    }

    /**
     * create_signature_object
     *
     * @param string $signature Passes a string signature from a JWT
     *
     * @return string Returns an array containing GMP encoded r and s values to represent the signature for mathematical use 
     */

    public function create_signature_object( $signature ) {

        $rawSig = $this->base64url_decode( $signature );
                
        $first_half = $this->string_to_hex( substr( $rawSig, 0, 32 ) );
        $second_half = $this->string_to_hex( substr( $rawSig, 32, 64 ) );
                
        return [
            'v' => 0,
            'rGMP' => gmp_init( '0x' . $first_half, 16 ),
            'sGMP' => gmp_init( '0x' . $second_half, 16 )
        ];

    }

    /**
     * create_signature_objectFromHex
     *
     * @param string $signature Passes a string signature from a JWT in hex format
     *
     * @return string Returns an array containing GMP encoded r and s values to represent the signature for mathematical use 
     */

    public function create_signature_objectFromHex( $signature ) {


        $rawSig = $this->base64url_decode( $signature );

        $firstHalf  = bin2hex( substr( $rawSig,  0, 32 ) );
        $secondHalf = bin2hex( substr( $rawSig, 32, 64 ) );

        return  [
            'v' => 0,
            'rGMP' => gmp_init( $firstHalf, 16 ),
            'sGMP' => gmp_init( $secondHalf, 16 )
        ];

    }

    /**
     * prepare_registry_callstring
     *
     * @param string $registration_identifier Passes a string MNID for the registrar
     *
     * @param string $issuer_id Passes a string MNID for the issuer
     *
     * @param string $subject_id Passes a string MNID for the subject (receiver)
     *
     * @return string Returns an object which can be passed to resolve_infura_payload()
     */

    private function prepare_registry_callstring( $registration_identifier, $issuer_id, $subject_id ) {

        $callObj    = ( object ) [];
        $issuer     = $this->eae_decode( $issuer_id );
        $subject    = $this->eae_decode( $subject_id );
        $networks   = $this->get_networks();

        if ( $issuer[ 'network' ] !== $subject[ 'network' ] ) {
            return 'Error: Subject and Issuer must be in the same network!';
        }

        if ( !$networks[ $issuer['network' ] ] ) {
           return 'Network id ' . $issuer[ 'network' ] . ' is not configured';
        } 
        
        $callObj->rpcUrl             = $networks[ $issuer[ 'network' ] ][ 'registry' ];
        $callObj->registryAddress    = $networks[ $issuer[ 'network' ] ][ 'registry' ];
        $callObj->function_signature = '0x447885f0';
        $callObj->callString         = $this->encode_function_call( $callObj->function_signature, $registration_identifier, $issuer[ 'address' ], $subject[ 'address' ] );

        return $callObj;

    }

    /**
     * make_http_call
     * This acts as a default HTTP call format for IPFS and Infura calls. An alternative can be supplied in the constructor event for further adaptability.
     *
     * @param string $url Passes the HTTP URL to call
     *
     * @param string $body Passes the payload body. Can be null if GET Request
     *
     * @param string $is_post Passes a boolean to indicate if this is a POST or GET request
     *
     * @return string Returns the result of the call
     */

    public function make_http_call( $url, $body, $is_post ) {

        $options = array(
                     CURLOPT_URL => $url,
                     CURLOPT_HEADER => false,
                     CURLOPT_FRESH_CONNECT => true,
                     CURLOPT_POSTFIELDS => $body,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_POST => $is_post,
                     CURLOPT_HTTPHEADER => array( 'Content-Type: application/json' )
                    );

        $ch = curl_init();

        curl_setopt_array( $ch, $options );

        $result = curl_exec( $ch );

        curl_close( $ch );

        return $result;
    }

    /**
     * sp_encode_and_trim
     *
     * @param string $payload Passes a value which needs to be encoded to base 64
     *
     * @return string Returns the encoded value after removing any extra '=' characters
     */

    private function sp_encode_and_trim( $payload ) {

        $encoded = base64_encode( $payload );
        if ( sizeof( explode( "=", $encoded ) ) > 1 ) {
            $trimmed = explode( "=", $encoded )[ 0 ];
        } else {
            $trimmed = $encoded;
        }
        return strtr( $trimmed, '+/', '-_' );
    }

    /**
     * encode_function_call
     *
     * @param string $function_signature Passes a predefined function signature for the infura call to be made
     *
     * @param string $registration_identifier Passes a string MNID for the registrar
     *
     * @param string $issuer_id Passes a string MNID for the issuer
     *
     * @param string $subject_id Passes a string MNID for the subject (receiver)
     *
     * @return string Returns the full Infura callstring in the proper format and encoding
     */

    private function encode_function_call( $function_signature, $registration_identifier, $issuer, $subject ) {

        $callString = $function_signature;

        $regStub = $this->string_to_hex( $registration_identifier );
        $issStub = subStr( $issuer, ( -1 )*( strlen( $issuer ) - 2 ) );
        $subStub = subStr( $subject, ( -1 )*( strlen( $issuer ) - 2 ) );

        $callString .= $this->pad( '0000000000000000000000000000000000000000000000000000000000000000', $regStub, false );
        $callString .= $this->pad( '0000000000000000000000000000000000000000000000000000000000000000', $issStub, true );
        $callString .= $this->pad( '0000000000000000000000000000000000000000000000000000000000000000', $subStub, true );
        return $callString;

    }

    /**
     * ascii2Hex
     *
     * @param string $string Passes a string to convert from ascii to hex
     *
     * @return string Returns the converted hex value as a string
     */

    private function ascii2Hex( $string ){
        $hex='';
        for ( $i=0; $i < strlen( $string ); $i++ ){
            $hex .= dechex( ord( $string[ $i ] ) );
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
     * @param string $pad_left Passes a boolean value to indicate whether the padding should be added to the left or right of the string
     *
     * @return string Returns the padded string
     */

    private function pad( $pad, $str, $pad_left ) {
        if ( gettype( $str ) == 'undefined' ) {
            return $pad;
        }
        if ( $pad_left === true ) {
            return substr( ( $pad . $str ), ( -1 ) * strlen( $pad ) );
        } else {
            return substr( ( $str . $pad ), 0, strlen( $pad ) );
        }
    }

    /**
     * eae_decode
     *
     * @param string $payload Passes payload to decode
     *
     * @return array Returns the decomposed address and network as an array
     */

    private function eae_decode( $payload ) {
        $base58 = new Base58( [
            'characters' => Base58::IPFS,
            'version'    => 0x00
        ] );

        $data       = unpack( "C*", $base58->decode( $payload ) );
        $netLength  = sizeof( $data ) - 24;
        $network    = array_slice( $data, 1, $netLength - 1 );
        $address    = array_slice( $data, $netLength, 20 + $netLength - 2 );
        $network    = '0x' . $this->encode_byte_array_to_hex( $network );
        $address    = '0x' . $this->encode_byte_array_to_hex( $address );
        return [
            'address' => $address,
            'network' => $network
        ];              
    }

    /**
     * get_networks
     *
     * @return array Returns the array of available Infura rpcUrls and registries for each network
     */

    private function get_networks() {
        return [
              '0x01' => [
                    'registry'  => '0xab5c8051b9a1df1aab0149f8b0630848b7ecabf6',
                    'rpcUrl'    => 'https://mainnet.infura.io'
              ], 
              '0x02' => [
                    'registry'  => '0x41566e3a081f5032bdcad470adb797635ddfe1f0',
                    'rpcUrl'    => 'https://ropsten.infura.io'
              ], 
              '0x03' => [
                    'registry'  => '0x5f8e9351dc2d238fb878b6ae43aa740d62fc9758',
                    'rpcUrl'    => 'https://kovan.infura.io'
              ],
              '0x04' => [
                    'registry'  => '0x2cc31912b2b0f3075a87b3640923d45a26cef3ee',
                    'rpcUrl'    => 'https://rinkeby.infura.io'
              ]
        ];
    }

}

