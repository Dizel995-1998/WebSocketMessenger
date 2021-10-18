<?php

namespace Lib\EntitiesExtractor;

class EntitiesExtractor implements EntitiesExtractorInterface
{
    protected ?string $scanDir = null;

    public function setScanDir(string $pathToDirectory): EntitiesExtractorInterface
    {
        if (!is_readable($pathToDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" is not readable', $pathToDirectory));
        }

        $this->scanDir = $pathToDirectory;
        return $this;
    }

    /**
     * todo: не умеет просматривать папки, работает на одном уровне вложенности
     * @param string $namespace
     * @return array
     */
    public function runScan(string $namespace): array
    {
        if ($this->scanDir == null) {
            throw new \RuntimeException('scanDir is not set, before call setScanDir method');
        }

        $res = [];

        foreach (scandir($this->scanDir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $res[] = $namespace . '\\' . str_replace('.php', '', $item);
        }

        return $res;
    }
}