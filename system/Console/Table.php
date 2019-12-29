<?php

namespace System\Console;

defined('DS') or exit('No direct script access allowed.');

class Table
{
    const HEADER_INDEX = -1;
    const HR = 'HR';

    protected $data = [];
    protected $border = true;
    protected $allBorders = false;
    protected $padding = 1;
    protected $indent = 0;

    private $rowIndex = -1;
    private $columnWidths = [];

    /**
     * Tambahkan header tabel.
     *
     * @param string $content
     */
    public function addHeader($content = '')
    {
        $this->data[self::HEADER_INDEX][] = $content;

        return $this;
    }

    /**
     * Alias untuk addHeader.
     *
     * @param array $content
     */
    public function setHeaders(array $content)
    {
        $this->data[self::HEADER_INDEX] = $content;

        return $this;
    }

    /**
     * Ambil header tabel.
     *
     * @return array
     */
    public function getHeaders()
    {
        return isset($this->data[self::HEADER_INDEX]) ? $this->data[self::HEADER_INDEX] : null;
    }

    /**
     * Tambahkan baris.
     *
     * @param array|null $data
     */
    public function addRow(array $data = null)
    {
        $this->rowIndex++;

        if (is_array($data)) {
            foreach ($data as $col => $content) {
                $this->data[$this->rowIndex][$col] = $content;
            }
        }

        return $this;
    }

    /**
     * Tambahkan kolom.
     *
     * @param array $content
     * @param int   $column
     * @param int   $row
     */
    public function addColumn($content, $column = null, $row = null)
    {
        $row = (null === $row) ? $this->rowIndex : $row;
        if (null === $column) {
            $column = isset($this->data[$row]) ? count($this->data[$row]) : 0;
        }

        $this->data[$row][$column] = $content;

        return $this;
    }

    /**
     * Tampilkan border tabel?
     */
    public function showBorder()
    {
        $this->border = true;

        return $this;
    }

    /**
     * Sembunyukan border tabel?
     */
    public function hideBorder()
    {
        $this->border = false;

        return $this;
    }

    /**
     * Tampilkan semua border tabel?
     */
    public function showAllBorders()
    {
        $this->showBorder();
        $this->allBorders = true;

        return $this;
    }

    /**
     * Set padding.
     *
     * @param int $value
     */
    public function setPadding($value = 1)
    {
        $this->padding = $value;

        return $this;
    }

    /**
     * Set indentasi tabel.
     *
     * @param int $value
     */
    public function setIndent($value = 0)
    {
        $this->indent = $value;

        return $this;
    }

    /**
     * Tambah garis border.
     */
    public function addBorderLine()
    {
        $this->rowIndex++;
        $this->data[$this->rowIndex] = self::HR;

        return $this;
    }

    /**
     * Cetak/tampilkan hasil tabel.
     */
    public function display()
    {
        echo $this->getTable();
    }

    /**
     * Render tabel.
     *
     * @return string
     */
    public function getTable()
    {
        $this->calculateColumnWidth();
        $output = $this->border ? $this->getBorderLine() : '';

        foreach ($this->data as $y => $row) {
            if (self::HR === $row) {
                if (!$this->allBorders) {
                    $output .= $this->getBorderLine();
                    unset($this->data[$y]);
                }

                continue;
            }

            foreach ($row as $x => $cell) {
                $output .= $this->getCellOutput($x, $row);
            }

            $output .= PHP_EOL;

            if (self::HEADER_INDEX === $y) {
                $output .= $this->getBorderLine();
            } else {
                if ($this->allBorders) {
                    $output .= $this->getBorderLine();
                }
            }
        }

        if (!$this->allBorders) {
            $output .= $this->border ? $this->getBorderLine() : '';
        }

        return is_cli() ? $output : '<pre>'.$output.'</pre>';
    }

    /**
     * Ambil garis border.
     *
     * @return string
     */
    private function getBorderLine()
    {
        $output = '';
        if (isset($this->data[0])) {
            $columnCount = count($this->data[0]);
        } elseif (isset($this->data[self::HEADER_INDEX])) {
            $columnCount = count($this->data[self::HEADER_INDEX]);
        } else {
            return $output;
        }

        for ($column = 0; $column < $columnCount; $column++) {
            $output .= $this->getCellOutput($column);
        }

        if ($this->border) {
            $output .= '+';
        }

        return $output.PHP_EOL;
    }

    /**
     * Ambil output sel.
     *
     * @param int $index
     * @param int $row
     *
     * @return string
     */
    private function getCellOutput($index, $row = null)
    {
        $cell = $row ? $row[$index] : '-';
        $width = $this->columnWidths[$index];
        $pad = $row ? $width - mb_strlen($cell, 'UTF-8') : $width;
        $padding = str_repeat($row ? ' ' : '-', $this->padding);

        $output = '';

        if (0 === $index) {
            $output .= str_repeat(' ', $this->indent);
        }

        if ($this->border) {
            $output .= $row ? '|' : '+';
        }

        $output .= $padding;
        $cell = trim(preg_replace('/\s+/', ' ', $cell));
        $content = preg_replace('#\x1b[[][^A-Za-z]*[A-Za-z]#', '', $cell);
        $delta = mb_strlen($cell, 'UTF-8') - mb_strlen($content, 'UTF-8');
        $output .= $this->strPadUnicode($cell, $width + $delta, $row ? ' ' : '-');
        $output .= $padding;

        if ($row && $index == count($row) - 1 && $this->border) {
            $output .= $row ? '|' : '+';
        }

        return $output;
    }

    /**
     * Hitung lebar kolom.
     *
     * @return int
     */
    private function calculateColumnWidth()
    {
        foreach ($this->data as $y => $row) {
            if (is_array($row)) {
                foreach ($row as $x => $col) {
                    $content = preg_replace('#\x1b[[][^A-Za-z]*[A-Za-z]#', '', $col);
                    if (!isset($this->columnWidths[$x])) {
                        $this->columnWidths[$x] = mb_strlen($content, 'UTF-8');
                    } else {
                        if (mb_strlen($content, 'UTF-8') > $this->columnWidths[$x]) {
                            $this->columnWidths[$x] = mb_strlen($content, 'UTF-8');
                        }
                    }
                }
            }
        }

        return $this->columnWidths;
    }

    /**
     * Strpad dengan dukungan unicone.
     *
     * @param string $str
     * @param int    $padLength
     * @param string $padString
     * @param int    $direction
     *
     * @return string
     */
    private function strPadUnicode($str, $padLength, $padString = ' ', $direction = STR_PAD_RIGHT)
    {
        $strLen = mb_strlen($str, 'UTF-8');
        $padStrLen = mb_strlen($padString, 'UTF-8');

        if (!$strLen && (STR_PAD_RIGHT == $direction || STR_PAD_LEFT == $direction)) {
            $strLen = 1;
        }

        if (!$padLength || !$padStrLen || $padLength <= $strLen) {
            return $str;
        }

        $result = null;
        $repeat = ceil($strLen - $padStrLen + $padLength);

        if (STR_PAD_RIGHT == $direction) {
            $result = $str.str_repeat($padString, $repeat);
            $result = mb_substr($result, 0, $padLength, 'UTF-8');
        } elseif (STR_PAD_LEFT == $direction) {
            $result = str_repeat($padString, $repeat).$str;
            $result = mb_substr($result, -$padLength, null, 'UTF-8');
        } elseif (STR_PAD_BOTH == $direction) {
            $length = ($padLength - $strLen) / 2;
            $repeat = ceil($length / $padStrLen);
            $result = mb_substr(str_repeat($padString, $repeat), 0, floor($length), 'UTF-8').
                $str.mb_substr(str_repeat($padString, $repeat), 0, ceil($length), 'UTF-8');
        }

        return $result;
    }
}
