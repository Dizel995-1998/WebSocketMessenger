<?php

namespace Lib\Container;

interface ContainerInterface
{
    public static function setService(string $resolveService, string $currentService = null, array $args = null) : void;

    public static function getService(string $resolveService) : object;

    public static function hasService(string $resolveService) : bool;
}