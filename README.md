# JwsToken 
This package is used to generate JWS tokens.

#### Initializing class 

```php 
use Spoova\JwsToken\JwsToken;

$jws = new JwsToken;
```

#### Modifying header for generating token 
Since a jws token is expected to created, it is essential to define the token header. This can be done by using the ```set()``` method. The header usually contain an header type and a specified algoritm as shown below: 

  ```php 
  $jws->set($type, $algo);
  ```
  + ```$type```  - The type of data supplied can either be ```JWS``` or ```JWT```. 
  + ```$algo```  - (optional) Any of the acceptable hashing algoritms in [HS256|HS384|HS512|RS256]

   > The example below is an example of setting any header

   ```php 
   $jws->set('JWS', 'HS256');
   ```
  + ```$type```  - The type of data supplied can either be ```JWS``` or ```JWT```. 
  + ```$algo```  - (optional) Any of the acceptable hasing algoritms.     

When the ```set()``` method is not defined, the arguments supplied above are assumed to be the default. 

#### Modifying algorithm for generating token only 
In the cases where we do not need to defined the entire header, we can modify only the algorithm without setting the full header by using the ```algo()``` method. In this case, only the default algo will be modified. For example: 

  ```php
  $jws = new JwsToken;

  $jws->algo('HS384'); //modify algorithm only
  ```

#### Setting Payload
The JwsToken payload is usually an array data that contains a list of specified data keys that contains relative information about a token that is expected to be hashed. The JwsToken ```payload()``` accepts an array under the following specific keys 

   + ```sub``` the subject of a token 
   + ```iss``` the issuer of a token 
   + ```aud``` the owner of a token 
   + ```nbf``` the time in which a generated token becomes active 
   + ```exp``` the time in which a generated token becomes expired 
   + ```data``` other information that is expected to be stored 

   The ```data``` key of a supplied payload should not contain any secret data. The example below reveals how to set a payload

  ```php
  $jws = new JwsToken;

  $payload = [

      'sub' => 'Token for accessing a class',
      'iss' => 'Issuer name or id'
      'aud' => 'Owner name or id'
      'iat' => time(),
      'nbf' => time() + 120, //token becomes active after 2 minutes of generation
      'exp' => time() + 240, //token becomes expired after 4 minutes of generation
      'data' => [
          'age' => 'user age',
          'gender' => 'male',
      ]
  ]

  $jws->payload($payload);
  ```

    In the example above,  

  + ```sub``` defines the title or subject of the token (optional)
  + ```iss``` defines the id of the issuer of a token 
  + ```aud``` defines the id of the owner of a token (optional)
  + ```iat``` defines the time a token is generated (optional)
  + ```nbf``` defines the time a token is active (optional)
  + ```exp``` defines the time a token expires (optional)
  + ```data``` should not contain any sensitive information. 

    Generally, the entire payload should not contain any sensitive information because it is only tokenfied but still visible to anyone. It is also important to note that it is not all the keys that are required. If the ```exp``` key is not defined, we can also set it by using the method ```expires()``` as shown below

    ```php
    $jws->payload($payload)->expires(time() + 240); //expires after  4 minutes of generation
    ```

    Payloads which do not have ```nbf``` will become active immediately it is generated while those that do not have ```exp``` will never expire. Also, payloads can contain any other custom keys aside the specifically reserved ones above.

#### Obtaining the hashed token 
Before a token can be obtained, it must be signed with a secret key using the ```sign()``` method after which the token is obtained using the ```token()``` method. 

   > Signing a token is shown below assuming the payload is already defined 

   ```php 
   $jws->sign('secret_key', 'sha256');
   ```

   When a secret key is signed, a secret key is expected to be defined. By default, the ```sign()``` method uses the crypto hashing algorithm ```sha256```, however this can be remodified by supplying a second argument into the ```sign()``` method which should be a valid hashing alogithm. Once a payload is signed, we can proceed to obtain the generated token. 

   ```php 
   $token = $jws->token(); //return the generated token 
   ```

#### Validating a generated token 
Once a token is generated, it can be validated using specifically designed methods

   > Setting a token for validation

   ```php 
   $jws->token($token); //set a token for validation
   ```

   In order to test if a token is valid, the ```isValid()``` method is used. This usually contains the secret key used during token generation and the hashing algorithm used.

   > Example of testing if a token is valid

   ```php 
   $jws->token($token);  
   
   if($jws->isValid('secret_key', 'sha256')){

      echo "token is valid";

   } else { 

     echo $jws->error();

   }
   ```   

   Usually, when a token is not valid it can be due to three reasons which is the reason we need to know why a token is not valid using the ```error()``` method. A token may not be valid for the following reasons 

   + Bad token format 
   + Token is not yet active 
   + Token is has expired.

   > We can detect if a token has expired by supplying the secret key and hashing algorithm into the ```expired()``` method. 

   ```php 
   $jws->token($token); 

   if($jws->expired('secret_key', 'sha256')){

       echo "token has expired";

   } else if(!$jws->error()) { 

       echo 'token is has not expired';

   }
   ```

   > We can detect if a token is not yet active by also supplying the secret key and hashing algorithm into the ```pending()``` method. 

   ```php 
   $jws->token($token); 

   if($jws->pending('secret_key', 'sha256')){

       echo "token has expired";

   } else if(!$jws->error()) { 

       echo 'token is has not expired';

   }
   ```

   Note that pending will return false if the payload is valid but is active. If no test is done yet, the ```pending()``` method will return empty string. However, ```true``` is only returned if the payload is valid and the token is not yet active or activated. 

#### Decrypting Token 

Valid tokens can be decrypted using the ```decrypt()``` method. Decryption here does not mean that the payload was not visible to users but it is only used to fetch a payload from a valid token. It is impossible to properly detect that any token supplied is a good one but if a token is valid, then we surely know we can obtain a valid payload from it which is done with the ```decrypt()``` method. This method takes the first argument as the token to be decrypted while the second argument is the secret key used to generate the token. Lastly, the third argument is the hash algorithm used to hash the token. 

    > Decrypting a valid token sample
   
   ```php
   $payload = $jws->decrypt($token, 'secret_key' 'sha256');
   
   var_dump($payload);
   ```   

   In the event that a token is checked for validity, the decrypt method can be used immediate to fetch the valid payload

   ```php 
   if($jws->token($token)->isValid('secret_key' 'sha256')) {

     $payload = $jws->decrypt();
   
     var_dump($payload);
    
   }else {

     echo $jws->error();

   }
   ```