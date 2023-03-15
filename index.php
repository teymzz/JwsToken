<?php

include_once "vendor/autoload.php";

use Spoova\JwsToken\JwsToken;

$jwsToken = new JwsToken;

$time = time();

$time_active  = $time + 60; //active 60 minutes after generation 

$time_expired = $time_active + 10; //expire 1 minute after active time

$payload = [

    'iat' => $time,
    'nbf' => $time_active,
    'exp' => $time_expired,
    'iss' => 'teymss@gmail.com',
    'data' => [
        'name' => 'victor'
    ]

];


$key = '1234';

$jwsToken->payload($payload)->sign($key);

$token = $jwsToken->token();
print_r($token);

?>