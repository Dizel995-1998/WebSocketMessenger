<?php

namespace Command;

use Lib\Command\BaseCommand;
use Lib\Container\Container;
use Lib\Migration\Migrator;

class MigrationUp extends BaseCommand
{
    protected string $migrationName;
    protected Migrator $migrator;

    public function __construct()
    {
        // TODO костыль
        $this->migrator = Container::getService(Migrator::class);
    }

    public function execute(): string
    {
        // TODO вызов DI
        // TODO попробывать $this->migrationName::class чтобы не писать пространство имён Migration
        $x = 'Migration\\' . $this->migrationName;
        $this->migrator->run(new $x());
        return '';
    }

    public function configure(): void
    {
        $this->migrationName = $this->input->getArgument('name', true);
        $arFiles = glob($_SERVER['DOCUMENT_ROOT'] . '/src/Migration/*_' . $this->migrationName . '.php');

        $countFiles = count($arFiles);
        if ($countFiles == 0) {
            throw new \InvalidArgumentException('Migration not found');
        }

        /** Не должно быть больше одной миграции, значит миграцию создали руками  */
        if ($countFiles > 1) {
            throw new \InvalidArgumentException('There is more migration than one');
        }

        $migrationPath = current($arFiles);
        require_once $migrationPath;
    }

    public function getCommandName(): string
    {
        return 'migrate:run';
    }
}