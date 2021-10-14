<?php

use Lib\Container\Container;
use Lib\Crypto\Crypto;
use Lib\Crypto\ICrypto;
use Lib\Request\Request;

$arDbConfig = [
    'dbHost' => 'mysql',
    'dbUser' => 'root',
    'dbPassword' => 'root',
    'dbName' => 'mydb'
];

$arRequestConfig = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'headers' => getallheaders(),
    'body' => file_get_contents('php://input'),
    'requestData' => $_REQUEST
];

Container::setService(ICrypto::class, ['alg' => 'SHA256'], Crypto::class);
Container::setService(Request::class, $arRequestConfig);

/**
 * TODO вынести это говно отсюда
 */

/**
 * @param $data
 * @return string
 */
function base64url_encode($data) : string
{
    if (($b64 = base64_encode($data)) === false) {
        return false;
    }

    $url = strtr($b64, '+/', '-_');
    return rtrim($url, '=');
}

/**
 * Decode data from Base64URL
 * @param string $data
 * @param boolean $strict
 * @return boolean|string
 */
function base64url_decode($data, $strict = false)
{
    $b64 = strtr($data, '-_', '+/');
    return base64_decode($b64, $strict);
}