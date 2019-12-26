<?php

namespace System\Console;

defined('DS') or exit('No direct script access allowed.');

trait AskTrait
{
    protected $questionSuffix = '> ';

    /**
     * Tanyakan pertayaan.
     *
     * @param string $question
     * @param mixed  $default
     *
     * @return mixed
     */
    public function ask($question, $default = null)
    {
        if ($default) {
            $question = $question.' '.$this->color("[$default]", 'green');
        }

        $this->write($question.PHP_EOL.$this->questionSuffix, 'blue');

        $handle = fopen('php://stdin', 'r');
        $answer = trim(fgets($handle));
        fclose($handle);

        return $answer ?: $default;
    }

    /**
     * Tanyakan pertanyaan konfirmasi.
     *
     * @param string $question
     * @param bool   $default
     *
     * @return bool
     */
    public function confirm($question, $default = false)
    {
        $availableAnswers = [
            'yes' => true,
            'no'  => false,
            'y'   => true,
            'n'   => false,
        ];

        $result = null;
        $suffix = null;
        do {
            if ($default) {
                $suffix = $this->color('[', 'dark_gray');
                $suffix .= $this->color('Y', 'green');
                $suffix .= $this->color('/n]', 'dark_gray');
            } else {
                $suffix = $this->color('[', 'dark_gray');
                $suffix .= $this->color('y', 'green');
                $suffix .= $this->color('/N]', 'dark_gray');
            }

            $answer = $this->ask($question.' '.$suffix) ?: ($default ? 'y' : 'n');

            if (!isset($availableAnswers[$answer])) {
                $this->error('Please answer with: y, n, yes, or no.');
            } else {
                $result = $availableAnswers[$answer];
            }
        } while (is_null($result));

        return $availableAnswers[$answer];
    }
}
