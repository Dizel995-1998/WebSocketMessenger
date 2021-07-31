<?php

namespace Command;

use Lib\Command\BaseCommand;

class CommandCreateNewCommand extends BaseCommand
{
    public function execute(): string
    {
        if (!$commandName = $this->input->getArgument('name')) {
            throw new \InvalidArgumentException('Command must have name');
        }

        $pathToCreateNewCommand = __DIR__ . '/' . $commandName . '.php';

        if (file_exists($pathToCreateNewCommand)) {
            throw new \RuntimeException('So command already exist');
        }

        // TODO добавить проверку прав на запись

        return file_put_contents($pathToCreateNewCommand, $this->getTemplate($commandName)) ?
            'Success created new cli command' . PHP_EOL :
            'Cannot create new cli command' . PHP_EOL;
    }

    private function getTemplate(string $commandName) : string
    {
        return <<<TEMPLATE
<?php

namespace Command;

use Lib\Command\BaseCommand;

class {$commandName} extends BaseCommand
{
    public function execute(): string
    {
        // TODO: Implement execute() method.
    }

    public function configure(): void
    {
        // TODO: Implement configure() method.
    }

    public function getCommandName(): string
    {
        // TODO: Implement getCommandName() method.
    }
}
TEMPLATE;
    }

    public function configure(): void
    {

    }

    public function getCommandName(): string
    {
        return 'make:command';
    }
}