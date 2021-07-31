<?php

namespace Lib\Command\Interfaces;

interface ICommand
{
    /**
     * Выполняет CLI скрипт
     * @return string STDOUT
     */
    public function execute() : string;

    /**
     * Вызывается до execute(),
     * служит для подготовки команды
     */
    public function configure() : void;

    /**
     * Возвращает название команды на которую будет откликаться CLI скрипт
     * @return string
     */
    public function getCommandName() : string;
}