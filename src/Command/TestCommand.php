<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected function configure() {
        $this
            ->setName('app:hello')
            ->setDescription('Sample command, hello')
            ->setHelp('This command is a sample command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Hello world');
        return self::SUCCESS;
    }
}