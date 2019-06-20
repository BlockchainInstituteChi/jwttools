# uPort PHP JWT Tools - Powered by TheBlockchainInstitute.org 

JSON Web Tokens (JWTs) are a convenient way of passing signed data requests over HTTP. The full details can be found on jwt.io. 

This module expects secp256k1 signed payloads per the uPort documentation at docs.uport.me. 


## Use

This repo can be installed as a composer module with the command below:

```composer require blockchaininstitute/jwttools```

## Functionality

```verify_jwt( $jwt )```
*This function provides the core use of this module. The $jwt parameter expects a decimal separated string as shown in the example below.*

```resolve_public_key_from_jwt($jwt)```
*This function returns a properly encoded publickey for a given jwt by resolving the uPort MNID via the infura gateway. The $jwt parameter expects a valid jwt object per jwt.io.*

```generate_infura_payload_from_jwt($jwt)```
*This function returns a php object containing the full IPFS DID for a given jwt by resolving the uPort MNID via the infura gateway. The $jwt parameter expects a valid jwt object per jwt.io.*

```create_jwt($jwt_header_json, $jwt_body_json, $signing_key)```
*This function returns a valid signed JWT using a hex encoded signing key. See the jwtComposer.php example file for further details.*


## Payload Formats

**The JWT:**
```
$jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTM4MDE4OTYsImV4cCI6MTU1Mzg4ODI5NiwiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRNNE1ERTRPVEFzSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTlvTURoelRVODBOMjVYY1VzMVYyOVRJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuaURoNWZ4UjZDdEpHV0VBcjg1VzBpd0JXMmhxOTl5UnE2T0ZQbUxpVGxlRmNoclItd3VYcWlGTmI1R203SUQ4VGxsR2RMRGpzSlU4NkV3U0E2dFU2b3ciLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.vFslRV7OGpfCAwQ9HDqr1BoBYNXlzyHjZiJrT4_0exgbrVXTYjbvJ3_6GGtI2yKATxjOUuX5EToNBcTXyPLBUg"
```

Where the format is < encoded header string >.< encoded body string >.< encoded signature string > with decimal points as separators.

**Decoded JWT (JSON):**
The header and body of the JWT shown above must be able to be url decoded and base 68 decoded to produce a JSON payload as shown below: 
```
jwt : {
  header: ,
  body: 
    {
      "iat":1553801896,
      "exp":1553888296,
      "aud":"2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ",
      "type":"shareResp",
      "nad":"2ot1hCuVAL6nQ3NQryjkBARGtsj4rsao575",
      "own": { 
        "name":"Alex"
      },
      "req":"eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTM4MDE4OTAsInJlcXVlc3RlZCI6WyJuYW1lIl0sImNhbGxiYWNrIjoiaHR0cHM6Ly9jaGFzcXVpLnVwb3J0Lm1lL2FwaS92MS90b3BpYy9oMDhzTU80N25XcUs1V29TIiwibmV0IjoiMHg0IiwidHlwZSI6InNoYXJlUmVxIiwiaXNzIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoifQ.iDh5fxR6CtJGWEAr85W0iwBW2hq99yRq6OFPmLiTleFchrR-wuXqiFNb5Gm7ID8TllGdLDjsJU86EwSA6tU6ow",
      "iss":"2ot1hCuVAL6nQ3NQryjkBARGtsj4rsao575"
    }
  signature: "vFslRV7OGpfCAwQ9HDqr1BoBYNXlzyHjZiJrT4_0exgbrVXTYjbvJ3_6GGtI2yKATxjOUuX5EToNBcTXyPLBUg
}
```

Full docs can be found here: https://github.com/uport-project/specs/blob/develop/messages/index.md#json-web-token

For more information about did resolution visit https://github.com/uport-project/specs/blob/develop/pki/index.md

**The Callback Function:**
To resolve the DID to a public key, it's necessary to make a call to the infura API. In order to ensure interoperability with wordpress plugins and other restricted environments such as Drupel, Laravel or Magento, the didResolver function will return an HTTP GET request which can be executed inside of the callback function. This can be seen in the DidResolver example below.


## Examples

### Resolve DID

```
<?php

  require 'vendor/autoload.php';
  require __DIR__ . '/vendor/autoload.php';

  use Blockchaininstitute\jwtTools as jwtTools;

  $jwtTools = new jwtTools('make_http_call');
  
  $jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTYyMTQ5MzcsImV4cCI6MTU1NjMwMTMzNywiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRZeU1UUTVNak1zSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTkwTUVsVmNtcEdjVEIzTjNkMlVsWnVJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuWTVtMTFKZmR1UG9hNW1fdm4zYkI4TUlqTHktUWdETHI3YTVMREhJcjgxclBkQWVrcmNKTzJra2UxQmJOOVVaSlVrNUQzZzVCRldqNW81RHM4cWQ0bUEiLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.dhS6KNpA21NJUmxtNmOCBv8ewBIwyOgqak9eXpUKZS8Hk-zpxjbbnkhLaOVHCENFjK2zzm9OxVekgGlwlNoIbw";

  $DID = $jwtTools->generate_infura_payload_from_JWT($jwt);

  print_r($DID);

  function make_http_call ($url, $body, $is_post) {

        $options = array(CURLOPT_URL => $url,
                     CURLOPT_HEADER => false,
                     CURLOPT_FRESH_CONNECT => true,
                     CURLOPT_POSTFIELDS => $body,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_POST => $is_post,
                     CURLOPT_HTTPHEADER => array( 'Content-Type: application/json')
                    );

        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
  }

``` 

### Compose and Sign a JWT

```
<?php
  require 'vendor/autoload.php';
  require __DIR__ . '/vendor/autoload.php';

  use Blockchaininstitute\jwtTools as jwtTools;

  $jwtTools = new jwtTools('make_http_call');

// Input Data
    $topicName = "Blockchain Institute Login Request";
    // For chasqui, this should be generated from an existing uportJs library for consistancy

// Prepare the JWT Header
  // 1. Initialize JWT Values
  $jwtHeader = (object)[];
  $jwtHeader->typ = 'JWT'; // ""
  $jwtHeader->alg = 'ES256K'; // ""

  // 2. Create JWT Object
  $jwtheader_json = json_encode($jwtHeader, JSON_UNESCAPED_SLASHES);


// Prepare the JWT Body
  // 1. Initialize JWT Values
  $jwtBody = (object)[];

   // "Client ID"
  $signingKey  = 'cb89a98b53eec9dc58213e67d04338350e7c15a7f7643468d8081ad2c5ce5480'; // "Private Key"
  // 776e591d9674b1c0fc8182f8574f24734cdeb4dc7ef8c4643d0fda33f4f8e0d6

  $jwtBody->iat         = 1556912833;
  $jwtBody->requested   = ['name'];
  $jwtBody->callback    = 'https://chasqui.uport.me/api/v1/topic/1OzSjQRFrF948LLk';
  // $jwtBody->callback     = $jwtTools->chasquiFactory($topicName);
  $jwtBody->net         = "0x4";
  $jwtBody->type      = "shareReq";
  $jwtBody->iss         = '2ojEtUXBK2J75eCBazz4tncEWE18oFWrnfJ';

  // 2. Create JWT Object
  $jwtbody_json = json_encode($jwtBody, JSON_UNESCAPED_SLASHES);


  echo "\r\n\r\njson_body:\r\n";
  print_r($jwtbody_json);
  echo "\r\n\r\n";

  $jwt = $jwtTools->create_jwt($jwtheader_json, $jwtbody_json, $signingKey);
    
    echo "\r\n\r\n======== BEGINNING VERIFICATION =======\r\n\r\n";

  $isVerified = $jwtTools->verifyJWT($jwt);

  echo "\r\n\r\nisVerified:\r\n" , $isVerified;

  echo "\r\n\r\n";

    function spEncodeAndTrim ($payload) {

      $encoded = base64_encode($payload);
      if ( sizeof(explode("=", $encoded)) > 1 ) {
        $trimmed = explode("=", $encoded)[0];
      } else {
        $trimmed = $encoded;
      }
      return $trimmed;
    }
    
  function make_http_call ($url, $body, $is_post) {

        $options = array(CURLOPT_URL => $url,
                     CURLOPT_HEADER => false,
                     CURLOPT_FRESH_CONNECT => true,
                     CURLOPT_POSTFIELDS => $body,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_POST => $is_post,
                     CURLOPT_HTTPHEADER => array( 'Content-Type: application/json')
                    );

        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
  }

``` 

### Resolve a Public Key from a JWT

```
<?php

    require 'vendor/autoload.php';
  require __DIR__ . '/vendor/autoload.php';

  use Blockchaininstitute\jwtTools as jwtTools;

  $jwtTools = new jwtTools('make_http_call');
  
  $jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTYyMTQ5MzcsImV4cCI6MTU1NjMwMTMzNywiYXVkIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoiLCJ0eXBlIjoic2hhcmVSZXNwIiwibmFkIjoiMm90MWhDdVZBTDZuUTNOUXJ5amtCQVJHdHNqNHJzYW81NzUiLCJvd24iOnsibmFtZSI6IkFsZXgifSwicmVxIjoiZXlKMGVYQWlPaUpLVjFRaUxDSmhiR2NpT2lKRlV6STFOa3NpZlEuZXlKcFlYUWlPakUxTlRZeU1UUTVNak1zSW5KbGNYVmxjM1JsWkNJNld5SnVZVzFsSWwwc0ltTmhiR3hpWVdOcklqb2lhSFIwY0hNNkx5OWphR0Z6Y1hWcExuVndiM0owTG0xbEwyRndhUzkyTVM5MGIzQnBZeTkwTUVsVmNtcEdjVEIzTjNkMlVsWnVJaXdpYm1WMElqb2lNSGcwSWl3aWRIbHdaU0k2SW5Ob1lYSmxVbVZ4SWl3aWFYTnpJam9pTW05cVJYUlZXRUpMTWtvM05XVkRRbUY2ZWpSMGJtTkZWMFV4T0c5R1YzSnVaa29pZlEuWTVtMTFKZmR1UG9hNW1fdm4zYkI4TUlqTHktUWdETHI3YTVMREhJcjgxclBkQWVrcmNKTzJra2UxQmJOOVVaSlVrNUQzZzVCRldqNW81RHM4cWQ0bUEiLCJpc3MiOiIyb3QxaEN1VkFMNm5RM05Rcnlqa0JBUkd0c2o0cnNhbzU3NSJ9.dhS6KNpA21NJUmxtNmOCBv8ewBIwyOgqak9eXpUKZS8Hk-zpxjbbnkhLaOVHCENFjK2zzm9OxVekgGlwlNoIbw";

  $address = $jwtTools->resolve_public_key_from_jwt($jwt);

  echo $address;


  function make_http_call ($url, $body, $is_post) {

        $options = array(CURLOPT_URL => $url,
                     CURLOPT_HEADER => false,
                     CURLOPT_FRESH_CONNECT => true,
                     CURLOPT_POSTFIELDS => $body,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_POST => $is_post,
                     CURLOPT_HTTPHEADER => array( 'Content-Type: application/json')
                    );

        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
  }
``` 


### Verify a JWT Signature

```
<?php
  require __DIR__ . '/vendor/autoload.php';

  use Blockchaininstitute\jwtTools as jwtTools;

  echo "\r\nStarting verifyJWT.php \r\n";

  $jwtTools = new jwtTools('make_http_call');

  $jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJFUzI1NksifQ.eyJpYXQiOjE1NTY5MTI4MzMsInJlcXVlc3RlZCI6WyJuYW1lIl0sImNhbGxiYWNrIjoiaHR0cHM6Ly9jaGFzcXVpLnVwb3J0Lm1lL2FwaS92MS90b3BpYy8xT3pTalFSRnJGOTQ4TExrIiwibmV0IjoiMHg0IiwidHlwZSI6InNoYXJlUmVxIiwiaXNzIjoiMm9qRXRVWEJLMko3NWVDQmF6ejR0bmNFV0UxOG9GV3JuZkoifQ.eeR7QXHZynWehtl7QsLbFSUgegudarGzuT2YqEUFPRUI3VOJwBVL+2zw0/RDz3kJX7sRdpZwdH0ANKdFz2w4UA";

  $isVerified = $jwtTools->verify_jwt($jwt);

  echo "\r\n\r\nisVerified:\r\n" , $isVerified;

  echo "\r\n\r\n";

  function make_http_call ($url, $body, $is_post) {

        $options = array(CURLOPT_URL => $url,
                     CURLOPT_HEADER => false,
                     CURLOPT_FRESH_CONNECT => true,
                     CURLOPT_POSTFIELDS => $body,
                     CURLOPT_RETURNTRANSFER => true,
                     CURLOPT_POST => $is_post,
                     CURLOPT_HTTPHEADER => array( 'Content-Type: application/json')
                    );

        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
  }
``` 






