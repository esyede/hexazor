<?php

namespace System\Support\Faker;

defined('DS') or exit('No direct script access allowed.');

use System\Support\Faker;

class Lipsum extends Module
{
    protected static $lipsum = null;

    public function paragraph()
    {
        $sentences = mt_rand(4, 10);
        $output = [];

        for ($i = 0; $i < $sentences; ++$i) {
            $output[] = $this->sentence();
        }

        return implode(' ', $output);
    }

    public function text()
    {
        return $this->paragraph();
    }

    public function sentence()
    {
        $words = mt_rand(5, 15);
        $commad = false;
        $output = [];

        for ($i = 0; $i < $words; ++$i) {
            $word = $this->word();
            if (0 == $i) {
                $word = ucwords($word);
            }

            $output[] = $word;

            if (!$commad && (($i + 1) != $words) && 1 == mt_rand(1, 5)) {
                $output[] = ',';
                $commad = true;
            }
        }

        return implode(' ', $output).'.';
    }

    public function word()
    {
        return static::pickOne('lipsum');
    }
}
