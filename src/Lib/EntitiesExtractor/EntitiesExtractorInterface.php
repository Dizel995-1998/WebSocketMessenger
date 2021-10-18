<?php

namespace Lib\EntitiesExtractor;

interface EntitiesExtractorInterface
{
    /**
     * @param string $pathToDirectory
     * @return $this
     */
    public function setScanDir(string $pathToDirectory) : self;

    /**
     * @param string $namespace
     * @return array
     */
    public function runScan(string $namespace) : array;
}