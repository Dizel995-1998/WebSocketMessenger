<?php

namespace Lib\Command;

use Lib\Command\Interfaces\ICommand;
use Lib\Command\Interfaces\InputInterface;
use Lib\Command\Interfaces\OutputInterface;

class CommandRunner
{
    private InputInterface $input;
    private OutputInterface $output;

    /**
     * @var array ICommand[]
     */
    private array $commands = [];

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function addCommand(ICommand $command)
    {
        $this->commands[] = $command;
    }

    public function run()
    {
        foreach ($this->commands as $command) {
            if ($command->getCommandName() == $this->input->getScriptName()) {
                $command->setInput($this->input);
                $command->setOutput($this->output);
                $command->configure();
                echo $command->execute();
                break;
            }
        }
    }
}