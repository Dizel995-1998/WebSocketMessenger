<?php

namespace Lib\Database\Query;

class QueryBuilder
{
    protected array $arMock = [];

    public function __construct(array $arMock = [])
    {
        if (!$arMock) {
            $arMock = [
                [
                    'MIME_TYPE' => 'image/jpeg',
                    'PATH' => '/var/www/bitrix/12.jpg',
                    'FILE_ID' => 123,
                    'EXTENSION' => 'jpeg'
                ],
                [
                    'MIME_TYPE' => 'image/jpg',
                    'PATH' => '/var/www/bitrix/995.jpg',
                    'FILE_ID' => 124,
                    'EXTENSION' => 'png'
                ]
            ];
        }

        $this->arMock = $arMock;
    }

    /**
     * @param string[] $fields
     * @return self
     */
    public function select(array $fields) : self
    {

        return $this;
    }

    /**
     * @param array-key[] $filter
     * @return $this
     */
    public function filter(array $filter) : self
    {

        return $this;
    }

    /**
     * TODO временно возвращает массив, будет возвращать обьект выборки
     * @return array
     */
    public function exec(): array
    {
        // Данные полученные якобы от БД
        return $this->arMock;
    }
}