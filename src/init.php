<?php

use Lib\Container\Container;

$arDbConfig = [
    'dbHost' => 'mysql',
    'dbUser' => 'root',
    'dbPassword' => 'root',
    'dbName' => 'mydb'
];

$arMigratorConfig = [
    'pathToMigrations' => '/var/www/src/Migration'
];

Container::setService(\Lib\Database\Interfaces\IConnection::class, $arDbConfig, \Lib\Database\Adapters\PdoConnection::class);
Container::setService(\Lib\Migration\Migrator::class, $arMigratorConfig, \Lib\Migration\Migrator::class);

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