<?php

namespace System\Support\Faker\Provider;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;

class Text extends Base
{
    protected static $baseText = '';
    protected static $separator = ' ';
    protected static $separatorLen = 1;
    
    protected $explodedText = null;
    protected $consecutiveWords = [];


    public function realText($maxNbChars = 200, $indexSize = 2)
    {
        if ($maxNbChars < 10) {
            throw new InvalidArgumentException('maxNbChars must be at least 10');
        }

        if ($indexSize < 1) {
            throw new InvalidArgumentException('indexSize must be at least 1');
        }

        if ($indexSize > 5) {
            throw new InvalidArgumentException('indexSize must be at most 5');
        }


        $words = $this->getConsecutiveWords($indexSize);
        $result = [];
        $resultLength = 0;

        $next = static::randomKey($words);

        while ($resultLength < $maxNbChars && isset($words[$next])) {
            $word = static::randomElement($words[$next]);
            $currentWords = static::explode($next);
            $currentWords[] = $word;
            array_shift($currentWords);
            $next = static::implode($currentWords);

            if ($resultLength === 0 && !static::validStart($word)) {
                continue;
            }

            $result[] = $word;
            $resultLength += static::strlen($word) + static::$separatorLen;
        }

        array_pop($result);
        $result = static::implode($result);

        return static::appendEnd($result);
    }


    protected function getConsecutiveWords($indexSize)
    {
        if (!isset($this->consecutiveWords[$indexSize])) {
            $parts = $this->getExplodedText();
            $words = [];
            $index = [];

            for ($i = 0; $i < $indexSize; $i++) {
                $index[] = array_shift($parts);
            }

            for ($i = 0, $count = count($parts); $i < $count; $i++) {
                $stringIndex = static::implode($index);
                
                if (!isset($words[$stringIndex])) {
                    $words[$stringIndex] = array();
                }
                
                $word = $parts[$i];
                $words[$stringIndex][] = $word;
                array_shift($index);
                $index[] = $word;
            }

            $this->consecutiveWords[$indexSize] = $words;
        }

        return $this->consecutiveWords[$indexSize];
    }

    
    protected function getExplodedText()
    {
        if ($this->explodedText === null) {
            $this->explodedText = static::explode(preg_replace('/\s+/u', ' ', static::$baseText));
        }

        return $this->explodedText;
    }

    
    protected static function explode($text)
    {
        return explode(static::$separator, $text);
    }

    
    protected static function implode($words)
    {
        return implode(static::$separator, $words);
    }

    
    protected static function strlen($text)
    {
        return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
    }

    
    protected static function validStart($word)
    {
        return preg_match('/^\p{Lu}/u', $word);
    }

    
    protected static function appendEnd($text)
    {
        return $text.'.';
    }
}
