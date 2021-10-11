<?php

namespace Lib\PhpDocReader;

class PhpDocParser
{
    const SYMBOL_TAG = '@';

    private string $phpDoc;

    public function __construct(string $phpDoc = null)
    {
        $this->phpDoc = $phpDoc ?? '';
    }

    public function setDoc(string $doc)
    {
        $this->phpDoc = $doc;
    }

    public function hasTag(string $tag) : bool
    {
        return (bool) strstr($this->phpDoc, self::SYMBOL_TAG . $tag);
    }

    public function getTag(string $tag) : ?array
    {
        $result = null;

        if (preg_match(sprintf('~%s%s\((?<value>.+)\)~m', self::SYMBOL_TAG, addslashes($tag)), $this->phpDoc, $matches)) {
            $result = json_decode('{' . $matches['value'] . '}', true);
        }

        return $result;
    }
}