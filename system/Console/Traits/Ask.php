<?php

namespace System\Console\Traits;

defined('DS') or exit('No direct script access allowed.');

trait Ask
{
    protected $questionSuffix = '> ';
    protected $showQuestion = true;
    protected $showConfirmation = true;


    public function question($question, $default = null)
    {
        if ($this->showQuestion) {
            if ($default) {
                $question = $question.' '.$default;
            }

            $this->writeline($question);
            $this->write($this->questionSuffix);

            $handle = fopen('php://stdin', 'r');
            $answer = trim(fgets($handle));
            fclose($handle);

            return $answer ?: $default;
        }

        return true;
    }


    public function confirmation($question, $default = false)
    {
        if ($this->showConfirmation) {
            $answers = ['y'=> true, 'n' => false];

            $result = null;
            $suffix = null;

            do {
                $suffix = $default ? '[Y/n]' : '[y/N]';
                $answer = $this->question($question.' '.$suffix) ?: ($default ? 'y' : 'n');
                $answer = strtolower($answer);

                if (!isset($answers[$answer])) {
                    $this->writeline('Please answer with: y or n.');

                    return false;
                } else {
                    $result = $answers[$answer];
                }
            } while (is_null($result));

            return $answers[$answer];
        }

        return true;
    }


    public function showQuestion($showed = true)
    {
        $this->showQuestion = (bool) $show;
    }


    public function showConfirmation($showed = true)
    {
        $this->showConfirmation = (bool) $show;
    }


    public function getConfirmationStatus()
    {
        return $this->showConfirmation;
    }


    public function getQuestionStatus()
    {
        return $this->showQuestion;
    }
}
