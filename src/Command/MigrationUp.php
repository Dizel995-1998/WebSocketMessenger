<?php

namespace Command;

use Lib\Command\BaseCommand;
use Lib\Container\Container;
use Service\MigrationManager;

class MigrationUp extends BaseCommand
{
    protected string $migrationName;
    protected MigrationManager $migrator;

    public function __construct()
    {
        $this->migrator = Container::getService(MigrationManager::class);
    }

    public function execute(): string
    {
        $this->migrator->runMigration('Migration\\' . $this->migrationName);
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