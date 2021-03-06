<?php

namespace System\Console\Commands;

defined('DS') or exit('No direct script access allowed.');

use System\Console\Command;
use System\Console\Console;
use System\Console\Table;
use System\Core\Application;
use System\Support\Collection;

class Help extends Command
{
    protected $signature = 'help {:command}';
    protected $description = 'Show help screen for command';

    public function handle()
    {
        $commands = Console::getCommands();
        $silent = Console::isSilentMode();
        Console::setSilentMode(false);

        if ($command = $this->getArgument('command')) {
            if (!in_array($command, array_keys($commands))) {
                $this->writeline("Call to unregistered command: '{$command}'");
                exit();
            }

            $command = new $commands[$command]();

            $usage = 'php '.Console::getFileName().' '.$command->getSignature();
            $description = $command->getDescription();
            $arguments = array_keys((array) $command->getArguments());
            $arguments = array_map(function ($argument) {
                return ltrim($argument, ':');
            }, $arguments);
            $options = $command->getOptions();

            $this->showUsage($usage, $description, $arguments, $options);
        } else {
            $this->showListing($commands);
        }

        Console::setSilentMode($silent);
    }


    protected function showUsage($usage, $description, array $arguments, array $options)
    {
        $this->newline();
        $usage = str_replace(['{:', '{--', '}'], ['[', '[--', ']'], $usage);
        $this->writeline('Usage:');
        $this->writeline($usage);
        $this->newline();

        $this->writeline('Description:');
        $description = blank($description) ? '-' : $description;
        $this->writeline($description);
        $this->newline();


        $this->writeline('Arguments:');
        $arguments =  blank($arguments) ? ['-'] : $arguments;
        $this->writeline(implode(', ', $arguments));
        $this->newline();

        $this->writeline('Options:');
        if (blank($options)) {
            $this->writeline('-');
        } else {
            foreach ($options as $key => $value) {
                $this->writeline($key.' '.(is_null($value) ? '(optional)' : '(default: '.$value.')'));
            }
        }
        $this->newline(2);
    }


    public function showListing(array $commands)
    {
        $this->writeline('Welcome to '.Application::PACKAGE.' Console v'.Application::VERSION);
        $this->newline();

        $table = new Table();
        $table
            ->addHeader('No.')
            ->addHeader('Command')
            ->addHeader('Description');
        $number = 1;

        foreach ($commands as $command) {
            $command = new $command();
            $commandName = $command->getCommand();

            if ($commandName === 'help') {
                $commandName = 'help <command>';
            }

            $table->addRow()
                ->addColumn($number.'.')
                ->addColumn($commandName)
                ->addColumn($command->getDescription());
            $number++;
        }

        $this->writeline($table->getTable());
        $this->writeline("Type 'php ".Console::getFilename()." help <command>' for help");
        $this->newline();
    }
}
