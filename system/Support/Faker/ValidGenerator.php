<?php

namespace System\Support\Faker;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;
use OverflowException;

class ValidGenerator
{
    protected $generator;
    protected $validator;
    protected $maxRetries;


    public function __construct(Generator $generator, $validator = null, $maxRetries = 10000)
    {
        if (is_null($validator)) {
            $validator = function () {
                return true;
            };
        } elseif (!is_callable($validator)) {
            throw new InvalidArgumentException('valid() only accepts callables as first argument');
        }
        
        $this->generator = $generator;
        $this->validator = $validator;
        $this->maxRetries = $maxRetries;
    }


    public function __get($attribute)
    {
        return $this->__call($attribute, []);
    }


    public function __call($name, $arguments)
    {
        $retry = 0;
        
        do {
            $result = call_user_func_array([$this->generator, $name], $arguments);
            $retry++;
            
            if ($retry > $this->maxRetries) {
                throw new OverflowException(
                    "Maximum retries of {$this->maxRetries} reached without finding a valid value"
                );
            }
        } while (!call_user_func($this->validator, $result));

        return $result;
    }
}
