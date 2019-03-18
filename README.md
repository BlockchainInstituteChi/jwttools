# php-did-resolver

This repo is intended to provide the ability for PHP-Based apps to retrieve DID documents for services such as the uPort Wordpress Plugin. 

The Lookup process accepts a dynamic multi-network identifier (MNID) value and a method in the following format:

did:< method >:< mnid >

where Alex's testnet did is 

did:uport:2ot1hCuVAL6nQ3NQryjkBARGtsj4rsao575

# To-Do's

1. Handle eaeDecode Step to properly format MNID 

In this step, we need to base58 decode the MNID and then break out the network and address.

Test Case: Must convert a string like 2ot1hCuVAL6nQ3NQryjkBARGtsj4rsao575 to a payload like 
{ network: '0x4',
  address: '0xa606b4521c03ae83d9f951882f9f8ca3ad80fd19' }

2. Write a switch to handle the network selection and proper RPC url

Networks include:
  '0x1': {
    registry: '0xab5c8051b9a1df1aab0149f8b0630848b7ecabf6',
    rpcUrl: 'https://mainnet.infura.io'
  },
  '0x3': {
    registry: '0x41566e3a081f5032bdcad470adb797635ddfe1f0',
    rpcUrl: 'https://ropsten.infura.io'
  },
  '0x2a': {
    registry: '0x5f8e9351dc2d238fb878b6ae43aa740d62fc9758',
    rpcUrl: 'https://kovan.infura.io'
    // },
    // '0x16B2': {
    //   registry: '',
    //   rpcUrl: 'https://infuranet.infura.io'
  },
  '0x4': {
    registry: '0x2cc31912b2b0f3075a87b3640923d45a26cef3ee',
    rpcUrl: 'https://rinkeby.infura.io'
  }


3. Write an encodeFunction

Implement a mirror of the encode function as shown below in Javascript from uPort-lite:

function encodeFunctionCall(functionSignature, registrationIdentifier, issuer, subject) {
    var callString = functionSignature;
    callString += pad('0000000000000000000000000000000000000000000000000000000000000000', asciiToHex(registrationIdentifier));
    callString += pad('0000000000000000000000000000000000000000000000000000000000000000', issuer.slice(2), true);
    callString += pad('0000000000000000000000000000000000000000000000000000000000000000', subject.slice(2), true);
    return callString;
  }

Where a sample payload could look like: {
	functionSignature: 0x447885f0 (statically defined)
	registrationIdentifier: uPortProfileIPFS1220 (statically defined)
	issuer: 0xa606b4521c03ae83d9f951882f9f8ca3ad80fd19
	subject: 0xa606b4521c03ae83d9f951882f9f8ca3ad80fd19 (from eaeDecode)
}

4. Compose HTTP payload and call the rpc url 

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
