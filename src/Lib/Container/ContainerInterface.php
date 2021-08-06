<?php

namespace Lib\Container;

interface ContainerInterface
{
    public function setService(string $resolveService, string $currentService = null, array $args = null) : void;

    public function getService(string $resolveService) : object;

    public function hasService(string $resolveService) : bool;
}