<?php

namespace System\Database;

defined('DS') or exit('No direct script access allowed.');

use System\Facades\Html;
use System\Facades\Request;

class Paginator
{
    public $results;
    public $page;
    public $last;
    public $total;
    public $perpage;

    protected $appends;
    protected $appendage;
    protected $dots = '<li class="dots disabled"><a href="#">...</a></li>';

    /**
     * Buat instance paginator baru.
     *
     * @param array $results
     * @param int   $page
     * @param int   $total
     * @param int   $perpage
     * @param int   $last
     */
    protected function __construct($results, $page, $total, $perpage, $last)
    {
        $this->page = $page;
        $this->last = $last;
        $this->total = $total;
        $this->results = $results;
        $this->perpage = $perpage;
    }

    /**
     * Buat instance paginator baru.
     *
     * @param array $results
     * @param int   $total
     * @param int   $perpage
     *
     * @return static
     */
    public static function make($results, $total, $perpage)
    {
        $page = static::page($total, $perpage);
        $last = ceil($total / $perpage);

        return new static($results, $page, $total, $perpage, $last);
    }

    /**
     * Ambil URL saat ini dari query string.
     *
     * @param int $total
     * @param int $perpage
     *
     * @return int
     */
    public static function page($total, $perpage)
    {
        $page = Request::get('page');
        $page = $page ?: 1;
        if (is_numeric($page) && $page > $last = ceil($total / $perpage)) {
            return ($last > 0) ? $last : 1;
        }

        return (static::valid($page)) ? $page : 1;
    }

    /**
     * Cek apakah nomor halaman valid.
     * Halaman yang valid harus berupa integer yang lebih besar atau sama dengan 1.
     *
     * @param int $page
     *
     * @return bool
     */
    protected static function valid($page)
    {
        return $page >= 1 && false !== filter_var($page, FILTER_VALIDATE_INT);
    }

    /**
     * Buat link paginasi html.
     *
     * @param int $adjacent
     *
     * @return string
     */
    public function links($adjacent = 3)
    {
        if ($this->last <= 1) {
            return '';
        }

        if ($this->last < 7 + ($adjacent * 2)) {
            $links = $this->range(1, $this->last);
        } else {
            $links = $this->slider($adjacent);
        }

        $content = '<ul>'.$this->previous().$links.$this->next().'</ul>'.PHP_EOL;

        return '<div class="pagination">'.$content.'</div>'.PHP_EOL;
    }

    /**
     * Buat daftar slider html berisi link angka halaman paginasi.
     *
     * @param int $adjacent
     *
     * @return string
     */
    public function slider($adjacent = 3)
    {
        $window = $adjacent * 2;

        if ($this->page <= $window) {
            return $this->range(1, $window + 2).' '.$this->ending();
        } elseif ($this->page >= $this->last - $window) {
            return $this->beginning().' '.$this->range($this->last - $window - 2, $this->last);
        }

        $content = $this->range($this->page - $adjacent, $this->page + $adjacent);

        return $this->beginning().' '.$content.' '.$this->ending();
    }

    /**
     * Buat link "Previous".
     *
     * @param string $text
     *
     * @return string
     */
    public function previous($text = null)
    {
        $disabled = function ($page) {
            return $page <= 1;
        };

        return $this->element(__FUNCTION__, $this->page - 1, $text, $disabled);
    }

    /**
     * Buat link "Next".
     *
     * @param string $text
     *
     * @return string
     */
    public function next($text = null)
    {
        $disabled = function ($page, $last) {
            return $page >= $last;
        };

        return $this->element(__FUNCTION__, $this->page + 1, $text, $disabled);
    }

    /**
     * Buat elemen urutan paginasi, seperti link "Previous" dan "Next".
     *
     * @param string   $element
     * @param int      $page
     * @param string   $text
     * @param \Closure $disabled
     *
     * @return string
     */
    protected function element($element, $page, $text, $disabled)
    {
        $class = "{$element}_page";

        if (is_null($text)) {
            $text = lang("paginator.{$element}");
        }

        if ($disabled($this->page, $this->last)) {
            return '<li'.Html::attributes(['class' => "{$class} disabled"]).
                '><a href="#">'.$text.'</a></li>'.PHP_EOL;
        }

        return $this->link($page, $text, $class);
    }

    /**
     * Buat dua link halaman pertama.
     *
     * @return string
     */
    protected function beginning()
    {
        return $this->range(1, 2).' '.$this->dots;
    }

    /**
     * Buat dua link halaman terakhir.
     *
     * @return string
     */
    protected function ending()
    {
        return $this->dots.' '.$this->range($this->last - 1, $this->last);
    }

    /**
     * Buat link rentang halaman paginasi
     * Halaman saat ini hanya akan berupa teks, bukan link.
     *
     * @param int $start
     * @param int $end
     *
     * @return string
     */
    protected function range($start, $end)
    {
        $pages = [];

        for ($page = $start; $page <= $end; $page++) {
            if ($this->page == $page) {
                $pages[] = '<li class="active"><a href="#">'.$page.'</a></li>'.PHP_EOL;
            } else {
                $pages[] = $this->link($page, $page, null);
            }
        }

        return implode(' ', $pages);
    }

    /**
     * Buat link halaman.
     *
     * @param int    $page
     * @param string $text
     * @param string $class
     *
     * @return string
     */
    protected function link($page, $text, $class)
    {
        $query = '?page='.$page.$this->appendage($this->appends);
        $base = base_url(ltrim(BASE_PATH, '/').$query);

        return '<li'.Html::attributes(['class' => $class]).'>'.
            Html::link($query, $text, [], Request::isSecure()).'</li>'.PHP_EOL;
    }

    /**
     * Buat "appendage" yang akan ditempatkan di semua link halaman.
     *
     * @param array $appends
     *
     * @return string
     */
    protected function appendage($appends)
    {
        if (!is_null($this->appendage)) {
            return $this->appendage;
        }

        $appends = is_null($appends) ? [] : $appends;

        if (count($appends) <= 0) {
            return $this->appendage = '';
        }

        return $this->appendage = '&'.http_build_query($appends);
    }

    /**
     * Set item apa saja yang harus di-append ke link query string.
     *
     * @param array $values
     *
     * @return $this
     */
    public function appends($values)
    {
        $this->appends = $values;

        return $this;
    }
}
