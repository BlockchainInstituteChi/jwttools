## uPort PHP JWT Tools (Examples Branch) - Powered by TheBlockchainInstitute.org

JWTs or JSON Web Tokens are a convenient way of passing signed data requests over HTTP. The full details can be found on jwt.io. 

This module expects secp256k1 signed payloads per the uPort documentation at docs.uport.me. 

This branch contains some example scripts to demonstrate the use cases of the library's core functionality.

## Use

This repo can be installed as a composer module from the master branch. 

At this time, there are no required constructor arguments. See the examples branch of this repo for detailed code. 

## Functionality

```verifyJWT( $jwt )```
*This function provides the core use of this module. The $jwt parameter expects a decimal separated string as shown in the example below.*

```resolvePublicKeyFromJWT($jwt)```
*This function returns a properly encoded publickey for a given jwt by resolving the uPort MNID via the infura gateway. The $jwt parameter expects a valid jwt object per jwt.io.*

```resolveDIDFromJWT($jwt)```
*This function returns a php object containing the full IPFS DID for a given jwt by resolving the uPort MNID via the infura gateway. The $jwt parameter expects a valid jwt object per jwt.io.*

```createJWT($jwtHeaderJson, $jwtBodyJson, $signingKey)```
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
In order to resolve the DID to a public key, it's necessary to make a call to the infura API. In order to ensure interoperability with wordpress plugins and other restricted environments such as Drupel, Laravel or Magento, the didResolver function will return an HTTP GET request which can be executed inside of the callback function. 

An example can be seen using cURL in the didResolverExample.php file.


