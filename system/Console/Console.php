<?php

namespace System\Console;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;
use InvalidArgumentException;

class Console
{
    use AskTrait;

    protected static $stty;
    protected static $shell;

    protected $filename;
    protected $command;
    protected $arguments = [];
    protected $options = [];
    protected $optionsAlias = [];
    protected $commands = [];
    protected $resolvedOptions = [];

    protected $foregrounds = [
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    ];

    protected $backgrounds = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    ];

    /**
     * Constuctor.
     *
     * @param array|null $argv
     */
    public function __construct(array $argv = null)
    {
        if (is_null($argv)) {
            if ('cli' === PHP_SAPI) {
                $argv = $GLOBALS['argv'];
            } else {
                $argv = $_GET;
            }
        }

        list(
            $this->filename,
            $this->command,
            $this->arguments,
            $this->options,
            $this->optionsAlias
        ) = $this->parseArgv($argv);

        // Perintah - perintah bawaan
        $this->register(new Commands\KeyGenerate());
        $this->register(new Commands\RouteList());

        $this->resolveUserCommands();

        $this->register(new Commands\Make\MakeCommand());
        $this->register(new Commands\Make\MakeController());
        $this->register(new Commands\Make\MakeModel());
        $this->register(new Commands\Make\MakeMigration());
        $this->register(new Commands\Make\MakeMiddleware());
        $this->register(new Commands\Make\MakeListener());
        $this->register(new Commands\Make\MakeMigration());
        $this->register(new Commands\Make\MakeSeeder());

        $this->register(new Commands\Migrate\MigrateInstall());
        $this->register(new Commands\Migrate\Migrate());
        $this->register(new Commands\Migrate\MigrateRollback());
        $this->register(new Commands\Migrate\MigrateReset());
        $this->register(new Commands\Migrate\MigrateRefresh());
        $this->register(new Commands\DbSeed());

        $this->register(new Commands\Serve());
        $this->register(new Commands\Search());
    }

    /**
     * Daftarkan command baru.
     *
     * @param \System\Console\Command $command
     */
    public function register(Command $command)
    {
        list($name, $args, $options) = $this->parseCommand($command->getSignature());

        if (!$name) {
            $class = get_class($command);

            throw new InvalidArgumentException("Command '{$class}' should have a signature.");
        }

        if (!method_exists($command, 'handle')) {
            $class = get_class($command);

            throw new InvalidArgumentException("Command '{$class}' should have a @handle method.");
        }

        $command->defineApp($this);

        $this->commands[$name] = [
            'handler'     => [$command, 'handle'],
            'description' => $command->getDescription(),
            'args'        => $args,
            'options'     => $options,
        ];
    }

    /**
     * Daftarkan command baru via closure.
     *
     * @param string  $signature
     * @param string  $description
     * @param Closure $handler
     */
    public function command($signature, $description, Closure $handler)
    {
        list($name, $args, $options) = $this->parseCommand($signature);

        $this->commands[$name] = [
            'handler'     => $handler,
            'description' => $description,
            'args'        => $args,
            'options'     => $options,
        ];
    }

    /**
     * Ambil semua command yang terdaftar.
     *
     * @return array
     */
    public function getRegisteredCommands()
    {
        return $this->commands;
    }

    /**
     * Ambil saran command (ketika user salah ketik command).
     *
     * @param string $keyword
     *
     * @return array
     */
    public function getCommandsLike($keyword)
    {
        $regex = preg_quote($keyword);
        $commands = $this->getRegisteredCommands();

        $matched = [];

        foreach ($commands as $name => $command) {
            if ((bool) preg_match('/'.$regex.'/', $name)) {
                $matched[$name] = $command;
            }
        }

        return $matched;
    }

    /**
     * Ambil nama file command.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Ambil opsi - opsi command.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Cek apakah command punya option yang diberikan.
     *
     * @param string $option
     *
     * @return array
     */
    public function hasOption($option)
    {
        $options = (array) $this->options;

        return array_key_exists($option, $options);
    }

    /**
     * Ambil argumen - argumen command.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Jalankan console app.
     *
     * @return bool
     */
    public function run()
    {
        return $this->execute($this->command);
    }

    /**
     * Eksekusi command.
     *
     * @param string $command
     *
     * @return bool
     */
    public function execute($command)
    {
        if (!$command) {
            $command = 'search';
        }

        if (!isset($this->commands[$command])) {
            return $this->showCommandsLike($command);
        }

        if (array_key_exists('help', $this->options)
        || array_key_exists('h', $this->optionsAlias)) {
            return $this->showHelp($command);
        }

        try {
            $handler = $this->commands[$command]['handler'];
            $arguments = $this->validateAndResolveArguments($command);

            $this->validateAndResolveOptions($command);

            if ($handler instanceof Closure) {
                $handler = $handler->bindTo($this);
            }

            call_user_func_array($handler, $arguments);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Ambil opsi command.
     *
     * @param string $key
     *
     * @return string|array
     */
    public function option($key)
    {
        return isset($this->resolvedOptions[$key]) ? $this->resolvedOptions[$key] : null;
    }

    public function addOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    public function removeOption($key)
    {
        unset($this->options[$key]);
        unset($this->resolvedOptions[$key]);

        return $this;
    }

    /**
     * Tulis teks ke console.
     *
     * @param string $message
     * @param string $foreground
     * @param string $background
     */
    public function write($message, $foreground = null, $background = null)
    {
        if ($foreground || $background) {
            $message = $this->color($message, $foreground, $background);
        }

        echo $message;
    }

    /**
     * Cetak pesan ke console.
     *
     * @param string $message
     * @param string $foreground
     * @param string $background
     */
    protected function writeln($message, $foreground = 'white', $background = 'black')
    {
        $this->write($message, $foreground, $background);
        $this->newline();
    }

    /**
     * Taambahkan new line.
     *
     * @param int $count
     */
    public function newline($count = 1)
    {
        $count = intval($count);
        $count = ($count < 1) ? 1 : $count;
        echo str_repeat(PHP_EOL, $count);
    }

    /**
     * Cetak beberapa baris teks.
     *
     * @param array  $messages
     * @param string $foreground
     * @param string $background
     */
    public function paragraph(array $messages, $foreground = 'default', $background = 'black')
    {
        foreach ($messages as $message) {
            $this->writeln($message, $foreground, $background);
        }
    }

    /**
     * Cetak pesan error.
     *
     * @param string $message
     */
    public function error($message)
    {
        $this->writeln($message, 'red');
    }

    /**
     * Cetak pesan warning.
     *
     * @param string $message
     */
    public function warning($message)
    {
        $this->writeln($message, 'yellow');
    }

    /**
     * Cetak pesan info.
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->writeln($message, 'blue');
    }

    /**
     * Cetak pesan success.
     *
     * @param string $message
     */
    public function success($message)
    {
        $this->writeln($message, 'green');
    }

    /**
     * Cetak pesan success.
     *
     * @param string $message
     */
    public function plain($message)
    {
        $this->writeln($message);
    }

    /**
     * Keluar dari aplikai.
     *
     * @param bool $exit
     */
    public function quit()
    {
        exit();
    }

    /**
     * Warnai teks.
     *
     * @param string $text
     * @param string $foreground
     * @param string $background
     */
    public function color($text, $foreground, $background = null)
    {
        if (windows_os()) {
            return $text;
        }

        $colored = '';
        $coloring = false;

        if (isset($this->foregrounds[$foreground])) {
            $coloring = true;
            $colored .= "\033[".$this->foregrounds[$foreground].'m';
        }

        if (isset($this->backgrounds[$background])) {
            $coloring = true;
            $colored .= "\033[".$this->backgrounds[$background].'m';
        }

        $colored .= $text.($coloring ? "\033[0m" : '');

        return $colored;
    }

    /**
     * Parse command kedalam bentuk array.
     *
     * @param string $command
     *
     * @return array
     */
    protected function parseCommand($command)
    {
        $exp = explode(' ', trim($command), 2);
        $command = trim($exp[0]);
        $args = [];
        $options = [];

        if (isset($exp[1])) {
            $pattern = "/\{(?<name>\w+)(?<arr>\*)?".
                "((=(?<default>[^\}]+))|(?<optional>\?))?".
                "(::(?<desc>[^}]+))?\}/i";

            preg_match_all($pattern, $exp[1], $matchArgs);

            $pattern = "/\{--((?<alias>[a-zA-Z])\|)?".
                "(?<name>\w+)((?<valuable>=)(?<default>[^\}]+)?)?".
                "(::(?<desc>[^}]+))?\}/i";

            preg_match_all($pattern, $exp[1], $matchOptions);

            foreach ($matchArgs['name'] as $i => $argName) {
                $default = $matchArgs['default'][$i];
                $expDefault = explode('::', $default, 2);

                if (count($expDefault) > 1) {
                    $default = $expDefault[0];
                    $description = $expDefault[1];
                } else {
                    $default = $expDefault[0];
                    $description = $matchArgs['desc'][$i];
                }

                $args[$argName] = [
                    'is_array'    => !empty($matchArgs['arr'][$i]),
                    'is_optional' => (!empty($matchArgs['optional'][$i]) || !empty($default)),
                    'default'     => $default ?: null,
                    'description' => $description,
                ];
            }

            foreach ($matchOptions['name'] as $i => $optName) {
                $default = $matchOptions['default'][$i];
                $expDefault = explode('::', $default, 2);

                if (count($expDefault) > 1) {
                    $default = $expDefault[0];
                    $description = $expDefault[1];
                } else {
                    $default = $expDefault[0];
                    $description = $matchOptions['desc'][$i];
                }

                $options[$optName] = [
                    'is_valuable' => !empty($matchOptions['valuable'][$i]),
                    'default'     => $default ?: null,
                    'description' => $description,
                    'alias'       => $matchOptions['alias'][$i] ?: null,
                ];
            }
        }

        return [$command, $args, $options];
    }

    /**
     * Parse server Argv.
     *
     * @param array $argv
     *
     * @return array
     */
    protected function parseArgv(array $argv)
    {
        $argv = array_map(function ($item) {
            return str_replace('\\', '/', $item);
        }, $argv);

        $filename = array_shift($argv);
        $command = array_shift($argv);
        $arguments = [];
        $options = [];
        $optionsAlias = [];

        while (count($argv)) {
            $arg = array_shift($argv);
            if ($this->isOption($arg)) {
                $optName = ltrim($arg, '-');
                if ($this->isOptionWithValue($arg)) {
                    list($optName, $optvalue) = explode('=', $optName);
                } else {
                    $optvalue = array_shift($argv);
                }

                $options[$optName] = $optvalue;
            } elseif ($this->isOptionAlias($arg)) {
                $alias = ltrim($arg, '-');
                $exp = explode('=', $alias);
                $aliases = str_split($exp[0]);

                if (count($aliases) > 1) {
                    foreach ($aliases as $aliasName) {
                        $optionsAlias[$aliasName] = null;
                    }
                } else {
                    $aliasName = $aliases[0];
                    if (count($exp) > 1) {
                        list($aliasName, $aliasValue) = $exp;
                    } else {
                        $aliasValue = array_shift($argv);
                    }

                    $optionsAlias[$aliasName] = $aliasValue;
                }
            } else {
                $arguments[] = $arg;
            }
        }

        return [$filename, $command, $arguments, $options, $optionsAlias];
    }

    /**
     * Cek apakah OS user mendukung Stty.
     *
     * @return bool
     */
    private function hasSttyAvailable()
    {
        if (null !== self::$stty) {
            return self::$stty;
        }

        exec('stty 2>&1', $output, $exitcode);

        return self::$stty = 0 === $exitcode;
    }

    /**
     * Ambil jenis shell yang digunakan user.
     *
     * @return string
     */
    private function getShell()
    {
        if (null !== self::$shell) {
            return self::$shell;
        }

        self::$shell = false;

        if (file_exists('/usr/bin/env')) {
            $shells = ['bash', 'sh', 'zsh', 'ksh', 'csh'];
            $test = "/usr/bin/env %s -c 'echo OK' 2> /dev/null";

            foreach ($shells as $shell) {
                if ('OK' === rtrim(shell_exec(sprintf($test, $shell)))) {
                    self::$shell = $shell;
                    break;
                }
            }
        }

        return self::$shell;
    }

    /**
     * Cek apakah argumen yang dioper valid atau tidak.
     *
     * @param string $arg
     *
     * @return bool
     */
    protected function isOption($arg)
    {
        return (bool) preg_match('/^--\w+/', $arg);
    }

    /**
     * Cek apakah argumen yang dioper merupakan alias dari opsi yang valid atau tidak.
     *
     * @param string $arg
     *
     * @return bool
     */
    protected function isOptionAlias($arg)
    {
        return (bool) preg_match('/^-[a-z]+/i', $arg);
    }

    /**
     * Cek apakah argumen yang dioper merupakan opsi yang valid dengan valunya atau tidak.
     *
     * @param string $arg
     *
     * @return bool
     */
    protected function isOptionWithValue($arg)
    {
        return false !== strpos($arg, '=');
    }

    /**
     * Validasi dan resolve argumen yang dioper user.
     *
     * @param string $command
     *
     * @return array
     */
    protected function validateAndResolveArguments($command)
    {
        $args = $this->arguments;
        $commandArgs = $this->commands[$command]['args'];
        $resolvedArgs = [];

        foreach ($commandArgs as $argName => $argOption) {
            if (!$argOption['is_optional'] && empty($args)) {
                return $this->error("Parameter '$argName' is required.");
            }

            if ($argOption['is_array']) {
                $value = $args;
            } else {
                $value = array_shift($args) ?: $argOption['default'];
            }

            $resolvedArgs[$argName] = $value;
        }

        return $resolvedArgs;
    }

    /**
     * Validasi dan resolve opsi yang dioper user.
     *
     * @param string $command
     */
    protected function validateAndResolveOptions($command)
    {
        $options = $this->options;
        $optionsAlias = $this->optionsAlias;
        $commandOptions = $this->commands[$command]['options'];
        $resolvedOptions = $options;

        foreach ($commandOptions as $optName => $optionSetting) {
            $alias = $optionSetting['alias'];

            if ($alias && array_key_exists($alias, $optionsAlias)) {
                $value = array_key_exists($alias, $optionsAlias)
                    ? $optionsAlias[$alias]
                    : $optionSetting['default'];
            } else {
                $value = array_key_exists($optName, $options)
                    ? $options[$optName]
                    : $optionSetting['default'];
            }

            if (!$optionSetting['is_valuable']) {
                $hasAlias = array_key_exists($alias, $optionsAlias);
                $hasOption = array_key_exists($optName, $options);
                $resolvedOptions[$optName] = $hasAlias || $hasOption;
            } else {
                $resolvedOptions[$optName] = $value;
            }
        }

        $this->resolvedOptions = $resolvedOptions;
    }

    /**
     * Tampilkan saran command.
     *
     * @param string $keyword
     */
    protected function showCommandsLike($keyword)
    {
        $commands = $this->getRegisteredCommands();
        $matched = $this->getCommandsLike($keyword);

        if (1 === count($matched)) {
            $keys = array_keys($matched);
            $values = array_values($matched);
            $name = array_shift($keys);
            $command = array_shift($values);

            $this->warning("Unknown command: '$keyword'. Did you mean '$name'?");
        } else {
            $commandList = $this->commands['search']['handler'];
            $commandList(count($matched) ? $keyword : null);

            $this->error("Unknown command: {$keyword}");
        }
    }

    /**
     * Tampilkan layar help.
     *
     * @param string $name
     */
    protected function showHelp($name)
    {
        $command = $this->commands[$name];
        $maxLen = 0;

        $args = $command['args'];
        $opts = $command['options'];
        $usageArgs = [$name];

        $displayArgs = [];
        $displayOpts = [];

        foreach ($args as $argName => $argSetting) {
            $usageArgs[] = '<'.$argName.'>';
            $displayArg = $argName;

            if ($argSetting['is_optional']) {
                $displayArg .= ' (optional)';
            }

            if (strlen($displayArg) > $maxLen) {
                $maxLen = strlen($displayArg);
            }

            $displayArgs[$displayArg] = $argSetting['description'];
        }

        $usageArgs[] = '[option]';

        foreach ($opts as $optName => $optSetting) {
            $displayOpt = $optSetting['alias']
                ? str_pad('-'.$optSetting['alias'].',', 4)
                : str_repeat(' ', 4);

            $displayOpt .= '--'.$optName;

            if (strlen($displayOpt) > $maxLen) {
                $maxLen = strlen($displayOpt);
            }

            $displayOpts[$displayOpt] = $optSetting['description'];
        }

        $pad = $maxLen + 3;
        $this->newline();
        $this->info('Description:');
        $this->plain($command['description']);
        $this->newline();

        $this->info('Usage:');
        $this->plain(implode(' ', $usageArgs));
        $this->newline();

        $this->info('Parameters:');
        foreach ($displayArgs as $argName => $argDesc) {
            $this->success($argName.str_repeat(' ', $pad - strlen($argName)).$argDesc);
        }

        $this->newline();

        $this->info('Options:');
        if (empty($displayOpt)) {
            $this->plain('-');
        } else {
            foreach ($displayOpts as $optName => $optDesc) {
                $this->success($optName.str_repeat(' ', $pad - strlen($optName)).$optDesc);
            }
        }

        $this->newline();
    }

    /**
     * Auto-discover command yang dibuat oleh user.
     *
     * @return void
     */
    public function resolveUserCommands()
    {
        $files = glob(app_path('Console/Commands/*.php'));
        foreach ($files as $file) {
            require_once $file;
        }

        $names = array_map(function ($name) {
            $class = '\\App\\Console\\Commands\\'.pathinfo($name, PATHINFO_FILENAME);

            return $this->register(new $class());
        }, $files);
    }

    /**
     * Tangani error konsol.
     *
     * @param \Exception $exception
     */
    public function handleError(Exception $exception)
    {
        $indent = str_repeat(' ', 2);
        $class = get_class($exception);
        $file = $exception->getFile();
        $line = $exception->getLine();

        $filepath = function ($file) {
            return str_replace(dirname(__DIR__).DS, '', $file);
        };

        $message = $exception->getMessage();
        $this->newline();

        $this->error($indent.'Whoops! you got: '.$class);
        $this->error($indent.$message);

        $this->warning($indent.'File: '.$filepath($file));
        $this->warning($indent.'Line: '.$line);
        $this->newline();

        $this->error('Stack trace:');
        dd($exception->getTrace());
    }
}
