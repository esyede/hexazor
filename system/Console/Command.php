<?php

namespace System\Console;

defined('DS') or exit('No direct script access allowed.');

class Command
{
    use Traits\Ask;
    use Traits\Writer;

    private $command;
    private $arguments;
    private $options;

    protected $signature;
    protected $description;


    public function __construct()
    {
        $parsedSignature = Helper::parseSignature($this->signature);
        $this->command = $parsedSignature[0];
        $this->arguments = $parsedSignature[1];
        $this->options = $parsedSignature[2];
    }


    public function update(array $arguments = null, array $options = null)
    {
        if ($arguments) {
            $keys = array_keys($this->arguments);

            for ($index = 0; $index < count($keys); $index++) {
                $this->arguments[$keys[$index]] = $arguments[$index];
            }
        }

        if ($options) {
            foreach ($options as $option => $value) {
                $this->options[$option] = $value;
            }
        }
    }


    public function handle()
    {
        return;
    }


    public function getSignature()
    {
        return $this->signature;
    }


    public function getCommand()
    {
        return $this->command;
    }


    public function getDescription()
    {
        return $this->description;
    }


    protected function getArgument($name)
    {
        if (isset($this->arguments[':'.$name])) {
            return $this->arguments[':'.$name];
        }

        return null;
    }


    public function getArguments()
    {
        return $this->arguments;
    }

    
    protected function getOption($name)
    {
        if (isset($this->options['--'.$name])) {
            return $this->options['--'.$name];
        }

        return null;
    }


    public function getOptions()
    {
        return $this->options;
    }
}
