<?php

namespace Lib\Migration;

interface IMigration
{
    /**
     * Накат миграции
     * @return void
     */
    public function up() : void;

    /**
     * Откат миграции
     * @return void
     */
    public function down() : void;
}