<?php

use Lib\Command\CommandRunner;

if (!$_SERVER['DOCUMENT_ROOT']) {
    $_SERVER['DOCUMENT_ROOT'] = realpath('./');
}

require_once 'vendor/autoload.php';

use Symfony\Component\Console\Application;

$app = new Application();
$app->add(new \Command\TestCommand());
$app->run();