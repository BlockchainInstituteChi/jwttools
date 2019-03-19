# php-did-resolver

This repo is intended to provide the ability for PHP-Based apps to retrieve DID documents for services such as the uPort Wordpress Plugin. 

The Lookup process accepts a dynamic multi-network identifier (MNID) value and a method in the following format:

did:< method >:< mnid >

where Alex's testnet did is 

did:uport:2ot1hCuVAL6nQ3NQryjkBARGtsj4rsao575

# To-Do's

1. Compose HTTP payload and call the rpc url 

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

# Installation 

While this is still in beta, you can install the composer module with the following composer commands:

```composer config repositories.did-resolver vcs https://github.com/BlockchainInstituteChi/php-did-resolver.git```
```composer require BlockchainInstituteChi/php-did-resolver:master```