<?php

namespace Lib\Command;

use Lib\Command\Interfaces\ICommand;
use Lib\Command\Interfaces\InputInterface;
use Lib\Command\Interfaces\OutputInterface;

abstract class BaseCommand implements ICommand
{
    protected ?InputInterface $input;
    protected ?OutputInterface $output;

    protected string $description;
    protected string $helpDescription;

    /**
     * TODO костыль
     * @param InputInterface $input
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }

    /**
     * TODO костыль
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    protected function setDescription(string $description) : self
    {
        $this->description = $description;
        return $this;
    }

    protected function setHelp(string $helpDescription) : self
    {
        $this->helpDescription = $helpDescription;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getHelpDescription() : string
    {
        return $this->helpDescription;
    }
}