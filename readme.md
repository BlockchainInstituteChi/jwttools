## PHP JWT Tools - Powered by TheBlockchainInstitute.org

JWT's or JSON Web Tokens are a convenient way of passing signed data requests over HTTP. The full details can be found on jwt.io. 

This module expects secp256k1 signed payloads per the uPort documentation at docs.uport.me. 

## Use

This repo can be installed as a composer module from the master branch. 

At this time, there are no required constructor arguments. See the examples branch of this repo for detailed code. 

## Functionality

```verifyJWT( $jwt, $publickey )```
*This function provides the core use of this module. The $jwt parameter expects a decimal separated string as shown in the example below. The $publickey parameter expects a string encoded public key, which can be found using the did-resolver function and the same JWT payload.*

```didResolver( $jwt, $callback)```
*This function returns a properly encoded publickey for a given jwt by resolving the uPort MNID via the infura gateway. The $jwt parameter expects a valid jwt object per jwt.io. The $callback parameter expects a string function name like 'myCallBackFunction' which can make an API call to the infura network and receive a payload like the one shown below.*

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

**The Callback Function:**
In order to resolve the DID to a public key, it's necessary to make a call to the infura API. In order to ensure interoperability with wordpress plugins and other restricted environments such as Drupel, Laravel or Magento, the didResolver function will return an HTTP GET request which can be executed inside of the callback function. 

An example might look something like the function shown below:
```
function infuraApiCallback ( $payload ) {
  ?? Hannah ??
}
```
The function can be passed into the did resolver as a string like 'infuraApiCallback' in order to trigger it on completion of the didResolver function. 

# To-Do's

1. Set up DidResolver to fully Compose HTTP payload and call the rpc url 

```
Example Payload: 
{ uri: 'https://rinkeby.infura.io/uport-lite-library',
  accept: 'application/json',
  data: 
   { method: 'eth_call',
     params: [ [Object], 'latest' ],
     id: 1,
     jsonrpc: '2.0' } }
params:  [ { to: '0x2cc31912b2b0f3075a87b3640923d45a26cef3ee',
    data: '0x447885f075506f727450726f66696c65495046533132323000000000000000000000000000000000000000000000000045cc630c5a692bb1fc5dcac3a368db549d6cfbf600000000000000000000000045cc630c5a692bb1fc5dcac3a368db549d6cfbf6' },
  'latest' ]
}
```

2. Create example in examples branch with a properly formatted http call in the callback function for didResolver

3. Create an example that shows the full validation process including the infura callback and address retrieval (update verifyJWT to properly use the resolve_did function and implement an appropriate callback scenario)

