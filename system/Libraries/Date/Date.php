<?php

namespace System\Libraries\Date;

defined('DS') or exit('No direct script access allowed.');

use stdClass;

class Date
{
    const ATOM = "Y-m-d\TH:i:sP";

    const COOKIE = 'l, d-M-y H:i:s T';

    const ISO8601 = "Y-m-d\TH:i:sO";

    const RFC822 = 'D, d M y H:i:s O';

    const RFC850 = 'l, d-M-y H:i:s T';

    const RFC1036 = 'D, d M y H:i:s O';

    const RFC1123 = 'D, d M Y H:i:s O';

    const RFC2822 = 'D, d M Y H:i:s O';

    const RFC3339 = "Y-m-d\TH:i:sP";

    const RSS = 'D, d M Y H:i:s O';

    const GENERIC = 'Y-m-d H:i:s';

    private $timestamp = '';

    private $comparisonDateTimestamp = '';

    private $comparisonArray = [];

    private $locale = 'en_EN.UTF-8';

    /**
     * Constructor.
     *
     * @param string $locale
     */
    public function __construct($locale = 'en_EN.UTF-8')
    {
        if (filled($locale)) {
            $this->locale = $locale;
        }

        setlocale(LC_TIME, $this->locale);
    }

    /**
     * Dipanggil otomatis ketika object kelas ini di-print sebagai string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

    /**
     * Ambil date sebagai string.
     *
     * @param string $format
     *
     * @return string
     */
    public function get($format = self::GENERIC)
    {
        $this->controlTimestamp();

        return date($format, $this->timestamp);
    }

    /**
     * Ambil bagian tahun dari tanggal.
     *
     * @return string
     */
    public function getYear()
    {
        $this->controlTimestamp();

        return date('Y', $this->timestamp);
    }

    /**
     * Ambil bagian bulan dari tanggal.
     *
     * @param bool $withZero
     *
     * @return string
     */
    public function getMonth($withZero = true)
    {
        $this->controlTimestamp();

        return date(($withZero ? 'm' : 'n'), $this->timestamp);
    }

    /**
     * Ambil bagian bulan dari tanggal (plus terapkan localization).
     *
     * @param bool $shorten
     *
     * @return string
     */
    public function getMonthString($shorten = false)
    {
        $this->controlTimestamp();

        return strftime(($shorten ? '%b' : '%B'), $this->timestamp);
    }

    /**
     * Ambil bagian hari dari tanggal.
     *
     * @param bool $withZero
     *
     * @return string
     */
    public function getDay($withZero = true)
    {
        $this->controlTimestamp();

        return date(($withZero ? 'd' : 'j'), $this->timestamp);
    }

    /**
     * Ambil bagian hari dari tanggal (plus terapkan localization).
     *
     * @param bool $shorten
     *
     * @return string
     */
    public function getDayString($shorten = false)
    {
        $this->controlTimestamp();

        return strftime(($shorten ? '%a' : '%A'), $this->timestamp);
    }

    /**
     * Ambil bagian jam dari tanggal.
     *
     * @param bool $wuse24HoursFormat
     * @param bool $withZero
     *
     * @return string
     */
    public function getHour($use24HoursFormat = true, $withZero = true)
    {
        $this->controlTimestamp();

        if ($use24HoursFormat) {
            return date(($withZero ? 'H' : 'G'), $this->timestamp);
        }

        return date(($withZero ? 'h' : 'g'), $this->timestamp);
    }

    /**
     * Ambil bagian menit dari tanggal.
     *
     * @return string
     */
    public function getMinute()
    {
        $this->controlTimestamp();

        return date('i', $this->timestamp);
    }

    /**
     * Ambil bagian detik dari tanggal.
     *
     * @return string
     */
    public function getSecond()
    {
        $this->controlTimestamp();

        return date('s', $this->timestamp);
    }

    /**
     * Ambil bagian mili-detik dari tanggal.
     *
     * @return string
     */
    public function getMiliSecond()
    {
        $this->controlTimestamp();

        return date('u', $this->timestamp);
    }

    /**
     * Ambil nilai timestamp dari datetime.
     *
     * @return string
     */
    public function getTimestamp()
    {
        $this->controlTimestamp();

        return $this->timestamp;
    }

    /**
     * Ambil hari dalam minggu (0 = minggu, 6 = senin).
     *
     * @return string
     */
    public function getDayOfWeek()
    {
        $this->controlTimestamp();

        return date('w', $this->timestamp);
    }

    /**
     * Ambil hari dalam tahun (0 - 364, atau 365 untuk tahun kabisat).
     *
     * @return string
     */
    public function getDayOfYear()
    {
        $this->controlTimestamp();

        return date('z', $this->timestamp);
    }

    /**
     * Ambil 'minggu ke-' dari tahun yang diberikan.
     *
     * @return string
     */
    public function getWeekOfYear()
    {
        $this->controlTimestamp();

        return date('W', $this->timestamp);
    }

    /**
     * Ambil banyaknya hari dari bulan yang diberikan (28, 29, 30 atau 31).
     *
     * @return string
     */
    public function getDaysInMonth()
    {
        $this->controlTimestamp();

        return date('t', $this->timestamp);
    }

    /**
     * Cek apakah tahun yang diberikan merupakan tahun kabisat (1 = kabisat, 0 = bukan).
     *
     * @return bool
     */
    public function isLeapYear()
    {
        $this->controlTimestamp();

        return date('L', $this->timestamp);
    }

    /**
     * Buat objek Date dari tanggal sekarang.
     *
     * @return $this
     */
    public function now()
    {
        $this->timestamp = strtotime('now');

        return $this;
    }

    /**
     * Buat objek Date dari string tanggal yang diberikan.
     *
     * @param string $dateString
     *
     * @return $this
     */
    public function make($dateString)
    {
        $this->timestamp = strtotime($dateString);

        return $this;
    }

    /**
     * Buat objek Date dari string timestamp yang diberikan.
     *
     * @param int $timestamp
     *
     * @return $this
     */
    public function makeFromTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Set tahun.
     *
     * @param int $year
     *
     * @return $this
     */
    public function setYear($year)
    {
        $month = $this->getMonth();
        $day = $this->getDay();
        $hour = $this->getHour();
        $minute = $this->getMinute();
        $second = $this->getSecond();

        $this->timestamp = mktime($hour, $minute, $second, $month, $day, $year);

        return $this;
    }

    /**
     * Set bulan.
     *
     * @param int $month
     *
     * @return $this
     */
    public function setMonth($month)
    {
        $year = $this->getYear();
        $day = $this->getDay();
        $hour = $this->getHour();
        $minute = $this->getMinute();
        $second = $this->getSecond();

        $this->timestamp = mktime($hour, $minute, $second, $month, $day, $year);

        return $this;
    }

    /**
     * Set hari.
     *
     * @param int $day
     *
     * @return $this
     */
    public function setDay($day)
    {
        $year = $this->getYear();
        $month = $this->getMonth();
        $hour = $this->getHour();
        $minute = $this->getMinute();
        $second = $this->getSecond();

        $this->timestamp = mktime($hour, $minute, $second, $month, $day, $year);

        return $this;
    }

    /**
     * Set jam.
     *
     * @param int $hour
     *
     * @return $this
     */
    public function setHour($hour)
    {
        $year = $this->getYear();
        $month = $this->getMonth();
        $day = $this->getDay();
        $minute = $this->getMinute();
        $second = $this->getSecond();

        $this->timestamp = mktime($hour, $minute, $second, $month, $day, $year);

        return $this;
    }

    /**
     * Set menit.
     *
     * @param int $minute
     *
     * @return $this
     */
    public function setMinute($minute)
    {
        $year = $this->getYear();
        $month = $this->getMonth();
        $day = $this->getDay();
        $hour = $this->getHour();
        $second = $this->getSecond();

        $this->timestamp = mktime($hour, $minute, $second, $month, $day, $year);

        return $this;
    }

    /**
     * Set detik.
     *
     * @param int $second
     *
     * @return $this
     */
    public function setSecond($second)
    {
        $year = $this->getYear();
        $month = $this->getMonth();
        $day = $this->getDay();
        $hour = $this->getHour();
        $minute = $this->getMinute();

        $this->timestamp = mktime($hour, $minute, $second, $month, $day, $year);

        return $this;
    }

    /**
     * Tambahkan tahun.
     *
     * @param int $year
     *
     * @return $this
     */
    public function addYear($year)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('+'.$year.' year', $this->timestamp);

        return $this;
    }

    /**
     * Tambahkan bulan.
     *
     * @param int $month
     *
     * @return $this
     */
    public function addMonth($month)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('+'.$month.' month', $this->timestamp);

        return $this;
    }

    /**
     * Tambahkan hari.
     *
     * @param int $day
     *
     * @return $this
     */
    public function addDay($day)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('+'.$day.' day', $this->timestamp);

        return $this;
    }

    /**
     * Tambahkan jam.
     *
     * @param int $hour
     *
     * @return $this
     */
    public function addHour($hour)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('+'.$hour.' hour', $this->timestamp);

        return $this;
    }

    /**
     * Tambahkan menit.
     *
     * @param int $minute
     *
     * @return $this
     */
    public function addMinute($minute)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('+'.$minute.' minute', $this->timestamp);

        return $this;
    }

    /**
     * Tambahkan detik.
     *
     * @param int $second
     *
     * @return $this
     */
    public function addSecond($second)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('+'.$second.' second', $this->timestamp);

        return $this;
    }

    /**
     * Kurangi tahun.
     *
     * @param int $year
     *
     * @return $this
     */
    public function subtractYear($year)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('-'.$year.' year', $this->timestamp);

        return $this;
    }

    /**
     * Kurangi bulan.
     *
     * @param int $month
     *
     * @return $this
     */
    public function subtractMonth($month)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('-'.$month.' month', $this->timestamp);

        return $this;
    }

    /**
     * Kurangi hari.
     *
     * @param int $day
     *
     * @return $this
     */
    public function subtractDay($day)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('-'.$day.' day', $this->timestamp);

        return $this;
    }

    /**
     * Kurangi jam.
     *
     * @param int $hour
     *
     * @return $this
     */
    public function subtractHour($hour)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('-'.$hour.' hour', $this->timestamp);

        return $this;
    }

    /**
     * Kurangi menit.
     *
     * @param int $minute
     *
     * @return $this
     */
    public function subtractMinute($minute)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('-'.$minute.' minute', $this->timestamp);

        return $this;
    }

    /**
     * Kurangi detik.
     *
     * @param int $second
     *
     * @return $this
     */
    public function subtractSecond($second)
    {
        $this->controlTimestamp();
        $this->timestamp = strtotime('-'.$second.' second', $this->timestamp);

        return $this;
    }

    /**
     * Bandingkan tanggal.
     *
     * @example  Date::now()->compare('2016-01-01')->isAfter();
     *
     * @param string $dateString
     *
     * @return bool
     */
    public function compare($dateString)
    {
        $this->comparisonDateTimestamp = strtotime($dateString);
        $this->calculateDifference();

        return $this;
    }

    /**
     * Bandingkan timestamp.
     *
     * @example  Date::now()->compareTimestamp('1393011488')->isBefore();
     *
     * @param string $dateString
     *
     * @return bool
     */
    public function compareTimestamp($timestamp)
    {
        $this->comparisonDateTimestamp = $timestamp;
        $this->calculateDifference();

        return $this;
    }

    /**
     * Ambil hasil perbandingan dalam bentuk array.
     *
     * @return array
     */
    public function getComparisonArray()
    {
        return $this->comparisonArray;
    }

    /**
     * Ambil perbedaan tahun antara 2 buah tanggal.
     *
     * @return int
     */
    public function getComparisonInYears()
    {
        return $this->comparisonArray['y'];
    }

    /**
     * Ambil perbedaan bulan antara 2 buah tanggal.
     *
     * @return int
     */
    public function getComparisonInMonths()
    {
        return $this->comparisonArray['m'] + ($this->getComparisonInYears() * 12);
    }

    /**
     * Ambil perbedaan hari antara 2 buah tanggal.
     *
     * @return int
     */
    public function getComparisonInDays()
    {
        return $this->comparisonArray['days'];
    }

    /**
     * Ambil perbedaan jam antara 2 buah tanggal.
     *
     * @return int
     */
    public function getComparisonInHours()
    {
        return $this->comparisonArray['h'] + ($this->getComparisonInDays() * 24);
    }

    /**
     * Ambil perbedaan menit antara 2 buah tanggal.
     *
     * @return int
     */
    public function getComparisonInMinutes()
    {
        return $this->comparisonArray['i'] + ($this->getComparisonInHours() * 60);
    }

    /**
     * Ambil perbedaan detik antara 2 buah tanggal.
     *
     * @return int
     */
    public function getComparisonInSeconds()
    {
        return $this->comparisonArray['s'] + ($this->getComparisonInMinutes() * 60);
    }

    /**
     * Cek apakah tanggal yang di-set adalah 'sebelum' tanggal pembandingnya
     * Returnnya int, 1 = berarti TRUE, 0 berarti FALSE.
     *
     * @return int
     */
    public function isBefore()
    {
        return $this->comparisonArray['isBefore'];
    }

    /**
     * Cek apakah tanggal yang di-set adalah 'sama dengan' tanggal pembandingnya
     * Returnnya int, 1 = berarti TRUE, 0 berarti FALSE.
     *
     * @return int
     */
    public function isEqual()
    {
        if (0 == $this->comparisonArray['y'] &&
            0 == $this->comparisonArray['m'] &&
            0 == $this->comparisonArray['d'] &&
            0 == $this->comparisonArray['h'] &&
            0 == $this->comparisonArray['i'] &&
            0 == $this->comparisonArray['s']) {
            return 1;
        }

        return 0;
    }

    /**
     * Cek apakah tanggal yang di-set adalah 'sebelum atau sama dengan' tanggal pembandingnya
     * Returnnya int, 1 = berarti TRUE, 0 berarti FALSE.
     *
     * @return int
     */
    public function isBeforeOrEqual()
    {
        if ($this->comparisonArray['isBefore'] || $this->isEqual()) {
            return 1;
        }

        return 0;
    }

    /**
     * Cek apakah tanggal yang di-set adalah 'setelah atau sama dengan' tanggal pembandingnya
     * Returnnya int, 1 = berarti TRUE, 0 berarti FALSE.
     *
     * @return int
     */
    public function isAfterOrEqual()
    {
        if (!$this->comparisonArray['isBefore'] || $this->isEqual()) {
            return 1;
        }

        return 0;
    }

    /**
     * Ambil perbedaan tanggal dengan format '... yang lalu'.
     *
     * @example  '2 tahun yang lalu'
     *
     * @param string|null $time
     *
     * @return string
     */
    public function timeAgo($time = null)
    {
        $time = is_null($time) ? $this->timestamp : strtotime($time);

        $diff = new stdClass();
        $diff->seconds = abs($time - time());
        $diff->minutes = floor($diff->seconds / 60);
        $diff->hours = floor($diff->minutes / 60);
        $diff->days = floor($diff->hours / 24);
        $diff->weeks = floor($diff->days / 7);

        // FIXME: Untuk bulan saya buletin ke 30 karena pembagian float di PHP busuk!
        // Sementara google bilang 1 bulan itu 30.4368499 hari.
        // Sebenarnya ada bantuan bcsub(), tapi kok rasanya kurang worth ya?
        // Soalnya, tidak semua shared hosting menyediakan ekstensi bcmath.

        $diff->months = floor($diff->days / 30);
        $diff->years = floor($diff->months / 12);

        $lang = lang('date');

        if ($diff->years > 0) {
            return sprintf($lang['years_ago'], $diff->years);
        } elseif ($diff->months > 0) {
            return sprintf($lang['months_ago'], $diff->months);
        } elseif ($diff->weeks > 0) {
            return sprintf($lang['weeks_ago'], $diff->weeks);
        } elseif ($diff->days > 0) {
            return sprintf($lang['days_ago'], $diff->days);
        } elseif ($diff->hours > 0) {
            return sprintf($lang['hours_ago'], $diff->hours);
        } elseif ($diff->minutes > 0) {
            return sprintf($lang['minutes_ago'], $diff->minutes);
        } elseif ($diff->seconds < 1) {
            return $lang['just_now'];
        }

        return sprintf($lang['seconds_ago'], $diff->seconds);
    }

    /**
     * Hitung perbedaan antara 2 buah tanggal.
     */
    private function calculateDifference()
    {
        $one = $this->timestamp;
        $two = $this->comparisonDateTimestamp;
        $invert = false;

        if ($one > $two) {
            list($one, $two) = [$two, $one];
            $invert = true;
        }

        $key = ['y', 'm', 'd', 'h', 'i', 's'];
        $a = array_combine($key, array_map('intval', explode(' ', date('Y m d H i s', $one))));
        $b = array_combine($key, array_map('intval', explode(' ', date('Y m d H i s', $two))));

        $result = [];
        $result['y'] = $b['y'] - $a['y'];
        $result['m'] = $b['m'] - $a['m'];
        $result['d'] = $b['d'] - $a['d'];
        $result['h'] = $b['h'] - $a['h'];
        $result['i'] = $b['i'] - $a['i'];
        $result['s'] = $b['s'] - $a['s'];

        $result['isBefore'] = $invert ? 0 : 1;
        $result['days'] = intval(abs(($one - $two) / 86400));

        $base = $invert ? $a : $b;
        $this->dateNormalize($base, $result);
        $this->comparisonArray = $result;
    }

    /**
     * Pastikan timestamp terisi.
     *
     * @return bool
     */
    private function controlTimestamp()
    {
        if ('' == $this->timestamp) {
            $this->now();
        }
    }

    /**
     * Batasi range tanggal.
     *
     * @param int   $start
     * @param int   $end
     * @param int   $adj
     * @param int   $a
     * @param int   $b
     * @param array &$result
     *
     * @return array
     */
    private function dateRangeLimit($start, $end, $adj, $a, $b, &$result)
    {
        if ($result[$a] < $start) {
            $result[$b] -= intval(($start - $result[$a] - 1) / $adj) + 1;
            $result[$a] += $adj * intval(($start - $result[$a] - 1) / $adj + 1);
        }

        if ($result[$a] >= $end) {
            $result[$b] += intval($result[$a] / $adj);
            $result[$a] -= $adj * intval($result[$a] / $adj);
        }

        return $result;
    }

    /**
     * Batasi range hari.
     *
     * @param int   &$base
     * @param array &$result
     *
     * @return array
     */
    private function dateRangeLimitDays(&$base, &$result)
    {
        $daysInMonthLeap = [31, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $daysInMonth = [31, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        $this->dateRangeLimit(1, 13, 12, 'm', 'y', $base);

        $year = $base['y'];
        $month = $base['m'];

        if ($result['isBefore']) {
            while ($result['d'] < 0) {
                --$month;
                if ($month < 1) {
                    $month += 12;
                    --$year;
                }

                $leapYear = 0 == $year % 400 || (0 != $year % 100 && 0 == $year % 4);
                $days = $leapYear ? $daysInMonthLeap[$month] : $daysInMonth[$month];

                $result['d'] += $days;
                --$result['m'];
            }
        } else {
            while ($result['d'] < 0) {
                $leapYear = 0 == $year % 400 || (0 != $year % 100 && 0 == $year % 4);
                $days = $leapYear ? $daysInMonthLeap[$month] : $daysInMonth[$month];

                $result['d'] += $days;
                --$result['m'];

                ++$month;
                if ($month > 12) {
                    $month -= 12;
                    ++$year;
                }
            }
        }

        return $result;
    }

    /**
     * Normalisasi batasan tanggal.
     *
     * @param int   &$base
     * @param array &$result
     *
     * @return array
     */
    private function dateNormalize(&$base, &$result)
    {
        $result = $this->dateRangeLimit(0, 60, 60, 's', 'i', $result);
        $result = $this->dateRangeLimit(0, 60, 60, 'i', 'h', $result);
        $result = $this->dateRangeLimit(0, 24, 24, 'h', 'd', $result);
        $result = $this->dateRangeLimit(0, 12, 12, 'm', 'y', $result);
        $result = $this->dateRangeLimitDays($base, $result);
        $result = $this->dateRangeLimit(0, 12, 12, 'm', 'y', $result);

        return $result;
    }
}
