<?php

class Configurator
{
    protected IStorage $settingStorage;

    public function __construct(IStorage $storage)
    {
        $this->settingStorage = $storage;
    }

    /**
     * @return string|int|float|null
     */
    public function get()
    {

    }
}