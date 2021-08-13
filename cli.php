<?php

use Lib\Command\CommandRunner;

if (!$_SERVER['DOCUMENT_ROOT']) {
    $_SERVER['DOCUMENT_ROOT'] = realpath('./');
}

require_once 'vendor/autoload.php';

$commandRunner = new CommandRunner(new \Lib\Command\Input($argv), new \Lib\Command\Output());

foreach (scandir($_SERVER['DOCUMENT_ROOT'] . '/src/Command') as $file) {
    if ($file == '.' || $file == '..') {
        continue;
    }

    $commandClassName = '\Command\\' . str_replace('.php', '', $file);
    $commandRunner->addCommand(new $commandClassName);
}

echo $commandRunner->run();