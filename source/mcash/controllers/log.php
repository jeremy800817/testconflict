<?php

function logaccess($username, $api, $response ){
    $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
    "uname: ".($username ).PHP_EOL.
    "Api: ".($api).PHP_EOL.
    "response: ".($response).PHP_EOL.
    "-------------------------".PHP_EOL;
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'log_.log', $log, FILE_APPEND);
}

function pesato( $token, $response ){
    $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
    "token: ".($token).PHP_EOL.
    "response: ".($response).PHP_EOL.
    "-------------------------".PHP_EOL;
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'pesato_.log', $log, FILE_APPEND);
}

function wallet($username,$uuid, $amount,  $channeluid){
    $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
    "uname: ".($username ).PHP_EOL.
    "uuid: ".($uuid ).PHP_EOL.
    "amount: ".($amount ).PHP_EOL.
    "channeluid: ".($channeluid).PHP_EOL.
    "-------------------------".PHP_EOL;
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'wallet_log.log', $log, FILE_APPEND);
}

function fpx($username, $uuid,$gram, $response){
    $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
    "uname: ".($username ).PHP_EOL.
    "uuid: ".($uuid ).PHP_EOL.
    "gram: ".($gram ).PHP_EOL.
    "response: ".($response).PHP_EOL.
    "-------------------------".PHP_EOL;
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'fpx_log.log', $log, FILE_APPEND);
}

function wallet_response($username, $channeluid, $response){
    $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
    "uname: ".($username ).PHP_EOL.
    "uuid: ".($channeluid ).PHP_EOL.    
    "response: ".($response).PHP_EOL.
    "-------------------------".PHP_EOL;
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'wallet_log.log', $log, FILE_APPEND);
}
function completepreAuthresponse($username, $channeluid, $response){
    $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
    "uname: ".($username ).PHP_EOL.
    "uuid: ".($channeluid ).PHP_EOL.    
    "response: ".($response).PHP_EOL.
    "-------------------------".PHP_EOL;
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'completepreauth.log', $log, FILE_APPEND);
}
?>