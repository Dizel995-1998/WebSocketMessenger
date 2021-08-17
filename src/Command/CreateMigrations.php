<?php

namespace Command;

use InvalidArgumentException;
use Lib\Command\BaseCommand;

class CreateMigrations extends BaseCommand
{
    private string $migrationClass;

    private function getMigrationTemplate(string $migrationName)
    {
        return <<<TEMPLATE
<?php

namespace Migration;

use Lib\Migration\IMigration;

class {$migrationName} implements IMigration
{
    public function up(): void
    {
        // TODO: Implement down() method.
    }

    public function down(): void
    {
        // TODO: Implement up() method.
    }
}
TEMPLATE;
    }

    public function execute(): string
    {
        // TODO добавить проверку на существование такой миграции
        $pathToMigrationsDir = realpath(__DIR__ . '/../../src/Migration');
        $pathToMigration = $pathToMigrationsDir . '/' . time() . '_' . $this->migrationClass . '.php';

        if (!is_readable($pathToMigrationsDir)) {
            throw new \RuntimeException(sprintf('Dont have enough right to write to this directory: %s', $pathToMigrationsDir));
        }

        return file_put_contents($pathToMigration, $this->getMigrationTemplate($this->migrationClass)) ?
            'Success created new migration command' . PHP_EOL :
            'Cannot write template to migration file' . PHP_EOL;
    }

    public function configure(): void
    {
        if (!$migrationClass = $this->input->getArgument('name')) {
            throw new InvalidArgumentException('Migration must have name, use -name arg');
        }

        $this->migrationClass = $migrationClass;
    }

    public function getCommandName(): string
    {
        return 'migrate:create';
    }
}