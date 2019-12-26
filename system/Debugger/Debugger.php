<?php

namespace System\Debugger;

defined('DS') or exit('No direct script access allowed.');

use System\Debugger\Interfaces\LoggerInterface;

class Debugger
{
    const DEVELOPMENT = false;
    const PRODUCTION = true;
    const DETECT = null;
    const COOKIE_SECRET = 'debugger-debug';

    public static $productionMode = self::DETECT;
    public static $showBar = true;

    private static $enabled = false;
    private static $reserved;
    private static $obLevel;

    public static $strictMode = false;
    public static $scream = false;
    public static $onFatalError = [];

    public static $maxDepth = 10;
    public static $maxLen = 300;
    public static $showLocation = false;

    public static $logFolder;
    public static $logSeverity = 0;
    public static $email;

    const DEBUG = LoggerInterface::DEBUG;
    const INFO = LoggerInterface::INFO;
    const WARNING = LoggerInterface::WARNING;
    const ERROR = LoggerInterface::ERROR;
    const EXCEPTION = LoggerInterface::EXCEPTION;
    const CRITICAL = LoggerInterface::CRITICAL;

    public static $time;
    public static $source;
    public static $editor = null;
    public static $browser;
    public static $errorTemplate;

    private static $cpuUsage;
    private static $blueScreen;
    private static $bar;
    private static $logger;
    private static $fireLogger;

    final public function __construct()
    {
        throw new \LogicException();
    }

    /**
     * Aktifkan tampilan atau logging error dan exception.
     *
     * @param string $mode
     * @param string $logDirectory
     * @param string $email
     */
    public static function enable($mode = null, $logDirectory = null, $email = null)
    {
        if (null !== $mode || null === self::$productionMode) {
            self::$productionMode = is_bool($mode) ? $mode : !self::detectDebugMode($mode);
        }

        self::$reserved = str_repeat('t', 3e5);
        self::$time = isset($_SERVER['REQUEST_TIME_FLOAT'])
            ? $_SERVER['REQUEST_TIME_FLOAT']
            : FRAMEWORK_START;

        self::$obLevel = ob_get_level();
        self::$cpuUsage = !self::$productionMode
            && function_exists('getrusage') ? getrusage() : null;

        if (null !== $email) {
            self::$email = $email;
        }

        if (null !== $logDirectory) {
            self::$logFolder = $logDirectory;
        }

        if (self::$logFolder) {
            if (!is_dir(self::$logFolder)
            || !preg_match('#([a-z]+:)?[/\\\\]#Ai', self::$logFolder)) {
                $text = 'Logging directory not found or is not absolute path: '.self::$logFolder;
                self::$logFolder = null;
                self::exceptionHandler(new \RuntimeException($text));
            }
        }

        if (function_exists('ini_set')) {
            ini_set('display_errors', !self::$productionMode);
            ini_set('html_errors', false);
            ini_set('log_errors', false);
        } elseif (ini_get('display_errors') != !self::$productionMode
        && ini_get('display_errors') !== (self::$productionMode ? 'stderr' : 'stdout')) {
            $text = "Unable to set 'display_errors' because function ini_set() is disabled.";
            self::exceptionHandler(new \RuntimeException($text));
        }

        error_reporting(E_ALL | E_STRICT);

        if (!self::$enabled) {
            register_shutdown_function([__CLASS__, 'shutdownHandler']);
            set_exception_handler([__CLASS__, 'exceptionHandler']);
            set_error_handler([__CLASS__, 'errorHandler']);

            array_map('class_exists', [
                '\System\Debugger\Bar',
                '\System\Debugger\BlueScreen',
                '\System\Debugger\DefaultBarPanel',
                '\System\Debugger\Dumper',
                '\System\Debugger\FireLogger',
                '\System\Debugger\Helpers',
                '\System\Debugger\Logger',
            ]);
            self::$enabled = true;
        }
    }

    /**
     * Dispatch ulang debugger.
     */
    public static function dispatch()
    {
        if (self::$productionMode || PHP_SAPI === 'cli') {
            return;
        } elseif (headers_sent($file, $line) || ob_get_length()) {
            throw new \Exception(
                __METHOD__.'() called after some output has been sent. '.
                    (
                        $file
                        ? "Output started at $file:$line."
                        : 'Try using OutputDebugger class to find where output started.'
                    )
            );
        } elseif (self::$enabled && PHP_SESSION_ACTIVE !== session_status()) {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_trans_sid', '0');
            ini_set('session.cookie_path', '/');
            ini_set('session.cookie_httponly', '1');
            session_start();
        }
    }

    /**
     * Apakah debugger sudah aktif?
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return self::$enabled;
    }

    /**
     * [INTERNAL] Shutdown handler muntuk menangkap fatal error.
     */
    public static function shutdownHandler()
    {
        if (!self::$reserved) {
            return;
        }

        $error = error_get_last();
        $errList = [
            E_ERROR,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_PARSE,
            E_RECOVERABLE_ERROR,
            E_USER_ERROR,
        ];

        if (in_array($error['type'], $errList, true)) {
            self::exceptionHandler(
                Helpers::fixStack(
                    new \ErrorException(
                        $error['message'],
                        0,
                        $error['type'],
                        $error['file'],
                        $error['line']
                    )
                ),
                false
            );
        } elseif (self::$showBar
        && !connection_aborted()
        && !self::$productionMode
        && self::isHtmlMode()) {
            self::$reserved = null;
            self::removeOutputBuffers(false);
            self::getBar()->render();
        }
    }

    /**
     * [INTERNAL] Handler untuk menangkap uncaught exception.
     *
     * @param \Exception|\Throwable $exception
     * @param bool                  $exit
     */
    public static function exceptionHandler($exception, $exit = true)
    {
        if (!self::$reserved) {
            return;
        }

        self::$reserved = null;

        if (!headers_sent()) {
            $protocol = isset($_SERVER['SERVER_PROTOCOL'])
                ? $_SERVER['SERVER_PROTOCOL']
                : 'HTTP/1.1';

            $code = isset($_SERVER['HTTP_USER_AGENT'])
                && false !== strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE ')
                    ? '503 Service Unavailable'
                    : '500 Internal Server Error';

            header("$protocol $code");

            if (self::isHtmlMode()) {
                header('Content-Type: text/html; charset=UTF-8');
            }
        }

        Helpers::improveException($exception);
        self::removeOutputBuffers(true);

        if (self::$productionMode) {
            try {
                self::log($exception, self::EXCEPTION);
            } catch (\Throwable $e) {
            } catch (\Exception $e) {
            }

            if (self::isHtmlMode()) {
                $logged = empty($e);
                require self::$errorTemplate
                    ?: __DIR__.DS.'assets'.DS.'debugger'.DS.'errors'.DS.'general.php';
            } elseif (PHP_SAPI === 'cli') {
                fwrite(STDERR, 'ERROR: application encountered an error and can not continue. '.
                    (isset($e) ? "Unable to log error.\n" : "Error was logged.\n"));
            }
        } elseif (!connection_aborted() && self::isHtmlMode()) {
            self::getBlueScreen()->render($exception);

            if (self::$showBar) {
                self::getBar()->render();
            }
        } else {
            self::fireLog($exception);
            $s = get_class($exception).
                ('' === $exception->getMessage() ? '' : ': '.$exception->getMessage()).
                ' in '.$exception->getFile().':'.$exception->getLine().
                "\nStack trace:\n".$exception->getTraceAsString();

            try {
                $file = self::log($exception, self::EXCEPTION);

                if ($file && !headers_sent()) {
                    header("X-Debugger-Error-Log: $file");
                }

                echo "$s\n".($file ? "(stored in $file)\n" : '');

                if ($file && self::$browser) {
                    exec(self::$browser.' '.escapeshellarg($file));
                }
            } catch (\Throwable $e) {
                echo "$s\nUnable to log error: {$e->getMessage()}\n";
            } catch (\Exception $e) {
                echo "$s\nUnable to log error: {$e->getMessage()}\n";
            }
        }

        try {
            $e = null;
            foreach (self::$onFatalError as $handler) {
                call_user_func($handler, $exception);
            }
        } catch (\Throwable $e) {
        } catch (\Exception $e) {
        }

        if ($e) {
            try {
                self::log($e, self::EXCEPTION);
            } catch (\Throwable $e) {
            } catch (\Exception $e) {
            }
        }

        if ($exit) {
            exit($exception instanceof \Error ? 255 : 254);
        }
    }

    /**
     * Handler untuk menangkap php warning dan notice.
     *
     * @param mixed $severity
     * @param mixed $message
     * @param mixed $file
     * @param mixed $line
     * @param mixed $context
     */
    public static function errorHandler($severity, $message, $file, $line, $context)
    {
        if (self::$scream) {
            error_reporting(E_ALL | E_STRICT);
        }

        if (E_RECOVERABLE_ERROR === $severity || E_USER_ERROR === $severity) {
            if (Helpers::findTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), '*::__toString')) {
                $previous = isset($context['e'])
                    && ($context['e'] instanceof \Exception
                        || $context['e'] instanceof \Throwable)
                    ? $context['e'] : null;

                $e = new \ErrorException($message, 0, $severity, $file, $line, $previous);
                $e->context = $context;

                self::exceptionHandler($e);
            }

            $e = new \ErrorException($message, 0, $severity, $file, $line);
            $e->context = $context;

            throw $e;
        } elseif (($severity & error_reporting()) !== $severity) {
            return false;
        } elseif (self::$productionMode && ($severity & self::$logSeverity) === $severity) {
            $e = new \ErrorException($message, 0, $severity, $file, $line);
            $e->context = $context;

            try {
                self::log($e, self::ERROR);
            } catch (\Throwable $e) {
            } catch (\Exception $foo) {
            }

            return;
        } elseif (!self::$productionMode && !isset($_GET['_debugger_skip_error'])
        && (
            is_bool(self::$strictMode)
            ? self::$strictMode
            : ((self::$strictMode & $severity) === $severity)
        )) {
            $e = new \ErrorException($message, 0, $severity, $file, $line);
            $e->context = $context;
            $e->skippable = true;
            self::exceptionHandler($e);
        }

        $message = 'PHP '.Helpers::errorTypeToString($severity).": $message";
        $count = &self::getBar()->getPanel('Debugger:errors')->data["$file|$line|$message"];

        if ($count++) {
            return;
        } elseif (self::$productionMode) {
            try {
                self::log("$message in $file:$line", self::ERROR);
            } catch (\Throwable $e) {
            } catch (\Exception $foo) {
            }

            return;
        } else {
            self::fireLog(new \ErrorException($message, 0, $severity, $file, $line));

            return self::isHtmlMode() ? null : false;
        }
    }

    /**
     * Apakah error saat ini terjadi di web browser?
     *
     * @return bool
     */
    private static function isHtmlMode()
    {
        return empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && PHP_SAPI !== 'cli'
            && !preg_match('#^Content-Type: (?!text/html)#im', implode("\n", headers_list()));
    }

    /**
     * Bersihkan output buffer.
     *
     * @param mixed $errorOccurred
     */
    private static function removeOutputBuffers($errorOccurred)
    {
        while (ob_get_level() > self::$obLevel) {
            $tmp = ob_get_status(true);
            $status = end($tmp);

            if (in_array($status['name'], ['ob_gzhandler', 'zlib output compression'])) {
                break;
            }

            $fnc = $status['chunk_size'] || !$errorOccurred ? 'ob_end_flush' : 'ob_end_clean';

            if (!@$fnc()) {
                break;
            }
        }
    }

    /**
     * Ambil objek BlueScreen.
     */
    public static function getBlueScreen()
    {
        if (!self::$blueScreen) {
            self::$blueScreen = new BlueScreen();
            self::$blueScreen->info = [
                'PHP '.PHP_VERSION,
                isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : null,
            ];
        }

        return self::$blueScreen;
    }

    /**
     * Ambil objek Bar.
     */
    public static function getBar()
    {
        if (!self::$bar) {
            self::$bar = new Bar();
            self::$bar->addPanel($info = new DefaultBarPanel('info'), 'Debugger:info');
            $info->cpuUsage = self::$cpuUsage;
            self::$bar->addPanel(new DefaultBarPanel('errors'), 'Debugger:errors');
        }

        return self::$bar;
    }

    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    /**
     * Ambil objek Logger.
     */
    public static function getLogger()
    {
        if (!self::$logger) {
            self::$logger = new Logger(self::$logFolder, self::$email, self::getBlueScreen());
            self::$logger->directory = &self::$logFolder;
            self::$logger->email = &self::$email;
        }

        return self::$logger;
    }

    /**
     * Ambil objek FireLogger.
     */
    public static function getFireLogger()
    {
        if (!self::$fireLogger) {
            self::$fireLogger = new FireLogger();
        }

        return self::$fireLogger;
    }

    /**
     * Dump informasi tentang sebuah variable, dalam format yang mudah dibaca.
     *
     * @param mixed $var
     * @param bool  $return
     *
     * @return mixed
     */
    public static function dump($var, $return = false)
    {
        if ($return) {
            ob_start(function () {
            });
            Dumper::dump($var, [
                Dumper::DEPTH    => self::$maxDepth,
                Dumper::TRUNCATE => self::$maxLen,
            ]);

            return ob_get_clean();
        } elseif (!self::$productionMode) {
            Dumper::dump($var, [
                Dumper::DEPTH    => self::$maxDepth,
                Dumper::TRUNCATE => self::$maxLen,
                Dumper::LOCATION => self::$showLocation,
            ]);
        }

        return $var;
    }

    /**
     * Start/stop timer (untuk benchmarking).
     *
     * @param string $name
     *
     * @return float
     */
    public static function timer($name = null)
    {
        static $time = [];

        $now = microtime(true);
        $delta = isset($time[$name]) ? $now - $time[$name] : 0;
        $time[$name] = $now;

        return $delta;
    }

    /**
     * Dump informasi tentang sebuah variable kedalam Debug Bar.
     *
     * @param mixed      $var
     * @param string     $title
     * @param array|null $options
     *
     * @return mixed
     */
    public static function barDump($var, $title = null, array $options = null)
    {
        if (!self::$productionMode) {
            static $panel;

            if (!$panel) {
                self::getBar()->addPanel($panel = new DefaultBarPanel('dumps'));
            }

            $panel->data[] = [
                'title' => $title,
                'dump'  => Dumper::toHtml($var, (array) $options + [
                    Dumper::DEPTH    => self::$maxDepth,
                    Dumper::TRUNCATE => self::$maxLen,
                    Dumper::LOCATION => self::$showLocation
                        ?: Dumper::LOCATION_CLASS | Dumper::LOCATION_SOURCE,
                ]),
            ];
        }

        return $var;
    }

    /**
     * Catat pesan atau exception ke /storage/logs.
     *
     * @param Exception|Throwable $message
     * @param string              $level
     *
     * @return mixed
     */
    public static function log($message, $level = LoggerInterface::INFO)
    {
        return self::getLogger()->log($message, $level);
    }

    /**
     * Kirim pesan ke konsol FireLogger.
     *
     * @param mixed $message
     *
     * @return bool
     */
    public static function fireLog($message)
    {
        if (!self::$productionMode) {
            return self::getFireLogger()->log($message);
        }
    }

    /**
     * Deteksi debug mode berdasarkan IP address.
     *
     * @param string|array $list
     *
     * @return bool
     */
    public static function detectDebugMode($list = null)
    {
        $addr = isset($_SERVER['REMOTE_ADDR'])
            ? $_SERVER['REMOTE_ADDR']
            : php_uname('n');

        $secret = isset($_COOKIE[self::COOKIE_SECRET])
            && is_string($_COOKIE[self::COOKIE_SECRET])
                ? $_COOKIE[self::COOKIE_SECRET]
                : null;

        $list = is_string($list)
            ? preg_split('#[,\s]+#', $list)
            : (array) $list;

        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        && !isset($_SERVER['HTTP_FORWARDED'])) {
            $list[] = '127.0.0.1';
            $list[] = '::1';
        }

        return in_array($addr, $list, true) || in_array("$secret@$addr", $list, true);
    }
}
