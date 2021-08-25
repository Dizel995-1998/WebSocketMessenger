<?php

namespace Lib\Database;

class ArrayCollection implements \Iterator
{
    protected array $elements = [];

    public function addElement($element)
    {
        $this->elements[] = $element;
    }

    public function current()
    {
        return current($this->elements);
    }

    public function next()
    {
        return next($this->elements);
    }

    public function key()
    {
        return key($this->elements);
    }

    public function valid()
    {
        return $this->current();
    }

    public function rewind()
    {
        reset($this->elements);
    }
}