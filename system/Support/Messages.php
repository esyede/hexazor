<?php

namespace System\Support;

defined('DS') or exit('No direct script access allowed.');

class Messages
{
    public $messages;
    public $format = ':message';

    /**
     * Buat instance baru.
     *
     * @param array $messages
     */
    public function __construct($messages = [])
    {
        $this->messages = (array) $messages;
    }

    /**
     * Tambahkan sebuah pesan ke collector.
     *
     * @param string $key
     * @param string $message
     */
    public function add($key, $message)
    {
        if ($this->unique($key, $message)) {
            $this->messages[$key][] = $message;
        }
    }

    /**
     * Cek apakah kombinasi key dan message sudah ada atau belum.
     *
     * @param string $key
     * @param string $message
     *
     * @return bool
     */
    protected function unique($key, $message)
    {
        return !isset($this->messages[$key]) || !in_array($message, $this->messages[$key]);
    }

    /**
     * Cek apakah sudah terdapat pesan berdasarkan key yang diberikan.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key = null)
    {
        return '' !== $this->first($key);
    }

    /**
     * Set format default untuk output pesan.
     *
     * @param string $format
     *
     * @return void
     */
    public function format($format = ':message')
    {
        $this->format = $format;
    }

    /**
     * Ambil pesan pertama dari container berdasarkan key yang diberikan.
     *
     * @param string $key
     * @param string $format
     *
     * @return string
     */
    public function first($key = null, $format = null)
    {
        $format = (null === $format) ? $this->format : $format;
        $messages = is_null($key) ? $this->all($format) : $this->get($key, $format);

        return (count($messages) > 0) ? $messages[0] : '';
    }

    /**
     * Ambil semua pesan dari container berdasarkan key yang diberikan.
     *
     * @param string $key
     * @param string $format
     *
     * @return array
     */
    public function get($key, $format = null)
    {
        $format = (null === $format) ? $this->format : $format;
        if (array_key_exists($key, $this->messages)) {
            return $this->transform($this->messages[$key], $format);
        }

        return [];
    }

    /**
     * Ambil semua pesan dari container.
     *
     * @param string $format
     *
     * @return array
     */
    public function all($format = null)
    {
        $format = (null === $format) ? $this->format : $format;

        $all = [];
        foreach ($this->messages as $messages) {
            $all = array_merge($all, $this->transform($messages, $format));
        }

        return $all;
    }

    /**
     * Format array pesan.
     *
     * @param array  $messages
     * @param string $format
     *
     * @return array
     */
    protected function transform($messages, $format)
    {
        $messages = (array) $messages;

        foreach ($messages as $key => &$message) {
            $message = str_replace(':message', $message, $format);
        }

        return $messages;
    }
}
