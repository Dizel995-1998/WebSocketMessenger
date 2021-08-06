<?php

namespace Lib\Container;

interface ContainerInterface
{
    /**
     * Устанавливает соответствие между интерфейсом сервиса и его конкретным экземпляром
     * @param string $resolveService
     * @param string|null $currentService
     * @param array|null $args
     */
    public static function setService(string $resolveService, string $currentService = null, array $args = null) : void;

    /**
     * Возвращает запрашиваемый обьект сервиса
     * @param string $resolveService
     * @return object
     */
    public static function getService(string $resolveService) : object;

    /**
     * Проверяет существует ли сервис
     * @param string $resolveService
     * @return bool
     */
    public static function hasService(string $resolveService) : bool;
}