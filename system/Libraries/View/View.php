<?php

namespace System\Libraries\View;

defined('DS') or exit('No direct script access allowed.');

use InvalidArgumentException;
use RuntimeException;
use System\Facades\Storage;

class View
{
    protected $fileExtension = null;
    protected $viewDirectory = null;
    protected $cacheDirectory = null;
    protected $echoFormat = null;
    protected $extensions = [];
    protected $templates = [];

    protected static $directives = [];

    protected $blocks = [];
    protected $blockStacks = [];
    protected $emptyCounter = 0;
    protected $firstCaseSwitch = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setFileExtension('.blade.php');
        $this->setViewDirectory(VIEW_PATH);
        $this->setEchoFormat('e(%s)');

        $directory = STORAGE_PATH.'system'.DS.'views'.DS;
        $this->ensureDirectoryExists($directory);
        $this->setCacheDirectory($directory);

        // reset
        $this->blocks = [];
        $this->blockStacks = [];
    }

    //!----------------------------------------------------------------
    //! Compilers
    //!----------------------------------------------------------------

    /**
     * Compile statement.
     *
     * @param string $value
     *
     * @return string
     */
    protected function compileStatements($value)
    {
        $pattern = '/\B@(@?\w+(?:->\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x';

        return preg_replace_callback($pattern, function ($match) {
            // perintah default
            if (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
                $match[0] = $this->$method(isset($match[3]) ? $match[3] : '');
            }

            // custom directive
            if (isset(self::$directives[$match[1]])) {
                if ((isset($match[3]) && '(' == $match[3][0])
                && ')' == $match[3][count($match[3]) - 1]) {
                    $match[3] = substr($match[3], 1, -1);
                }

                if (isset($match[3]) && '()' !== $match[3]) {
                    $match[0] = call_user_func(self::$directives[$match[1]], trim($match[3]));
                }
            }

            return isset($match[3]) ? $match[0] : $match[0].$match[2];
        }, $value);
    }

    /**
     * Compile comment.
     *
     * @param string $value
     *
     * @return string
     */
    protected function compileComments($value)
    {
        $pattern = '/\{\{--((.|\s)*?)--\}\}/';

        return preg_replace($pattern, '<?php /*$1*/ ?>', $value);
    }

    /**
     * Compile echo.
     *
     * @param string $value Echo data
     *
     * @return string
     */
    protected function compileEchos($value)
    {
        // compile escaped echo
        $pattern = '/\{\{\{\s*(.+?)\s*\}\}\}(\r?\n)?/s';
        $value = preg_replace_callback($pattern, function ($matches) {
            $whitespace = empty($matches[2]) ? '' : $matches[2].$matches[2];

            return '<?php echo e('.
                $this->compileEchoDefaults($matches[1]).
            ') ?>'.$whitespace;
        }, $value);

        // compile unescaped echo
        $pattern = '/\{\!!\s*(.+?)\s*!!\}(\r?\n)?/s';
        $value = preg_replace_callback($pattern, function ($matches) {
            $whitespace = empty($matches[2]) ? '' : $matches[2].$matches[2];

            return '<?php echo '.$this->compileEchoDefaults($matches[1]).' ?>'.$whitespace;
        }, $value);

        // compile regular echo
        $pattern = '/(@)?\{\{\s*(.+?)\s*\}\}(\r?\n)?/s';
        $value = preg_replace_callback($pattern, function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            return $matches[1]
                ? substr($matches[0], 1)
                : '<?php echo '
                  .sprintf($this->echoFormat, $this->compileEchoDefaults($matches[2]))
                  .' ?>'.$whitespace;
        }, $value);

        return $value;
    }

    /**
     * Compile echo default.
     *
     * @param string $value
     *
     * @return string
     */
    public function compileEchoDefaults($value)
    {
        $pattern = '/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s';

        return preg_replace($pattern, 'isset($1) ? $1 : $2', $value);
    }

    /**
     * Compile custom directive.
     *
     * @param string $value
     *
     * @return string
     */
    protected function compileExtensions($value)
    {
        foreach ($this->extensions as $compiler) {
            $value = $compiler($value, $this);
        }

        return $value;
    }

    /**
     * Replace blok @php dan @endphp.
     *
     * @param string $value
     *
     * @return string
     */
    public function replacePhpBlocks($value)
    {
        $pattern = '/(?<!@)@php(.*?)@endphp/s';
        $value = preg_replace_callback($pattern, function ($matches) {
            return "<?php{$matches[1]}?>";
        }, $value);

        return $value;
    }

    //!----------------------------------------------------------------
    //! Concerns
    //!----------------------------------------------------------------

    /**
     * Penggunaan: @php($var = 'value').
     *
     * @param string $value Some PHP expression
     *
     * @return string
     */
    protected function compilePhp($value)
    {
        if ($value) {
            return "<?php {$value}; ?>";
        }

        return "@php{$value}";
    }

    /**
     * Penggunaan: @bd($var).
     *
     * @param string $value Some PHP variables
     *
     * @return string
     */
    protected function compileBd($value)
    {
        return "<?php bd{$value} ?>";
    }

    /**
     * Penggunaan: @json($data).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileJson($value)
    {
        $default = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        if (isset($value) && '(' == $value[0]) {
            $value = substr($value, 1, -1);
        }

        $parts = explode(',', $value);
        $options = isset($parts[1]) ? trim($parts[1]) : $default;
        $depth = isset($parts[2]) ? trim($parts[2]) : 512;

        // PHP < 5.5.0 belum support parameter $depth
        if (PHP_VERSION_ID >= 50500) {
            return "<?php echo json_encode($parts[0], $options, $depth) ?>";
        }

        return "<?php echo json_encode($parts[0], $options) ?>";
    }

    /**
     * Penggunaan: @unset($var).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileUnset($value)
    {
        return "<?php unset{$value}; ?>";
    }

    /**
     * Penggunaan: @if($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileIf($value)
    {
        return "<?php if{$value}: ?>";
    }

    /**
     * Penggunaan: @elseif(condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileElseif($value)
    {
        return "<?php elseif{$value}: ?>";
    }

    /**
     * Penggunaan: @else.
     *
     * @return string
     */
    protected function compileElse()
    {
        return '<?php else: ?>';
    }

    /**
     * Penggunaan: @endif.
     *
     * @return string
     */
    protected function compileEndif()
    {
        return '<?php endif; ?>';
    }

    /**
     * Penggunaan: @switch($cases).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileSwitch($value)
    {
        $this->firstCaseSwitch = true;

        return "<?php switch{$value}:";
    }

    /**
     * Penggunaan: @case($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileCase($value)
    {
        if ($this->firstCaseSwitch) {
            $this->firstCaseSwitch = false;

            return "case {$value}: ?>";
        }

        return "<?php case {$value}: ?>";
    }

    /**
     * Penggunaan: @default.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileDefault()
    {
        return '<?php default: ?>';
    }

    /**
     * Penggunaan: @break or @break($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileBreak($value)
    {
        if ($value) {
            $pattern = '/\(\s*(-?\d+)\s*\)$/';
            preg_match($pattern, $value, $matches);

            return $matches
                ? '<?php break '.max(1, $matches[1]).'; ?>'
                : "<?php if{$value} break; ?>";
        }

        return '<?php break; ?>';
    }

    /**
     * Penggunaan: @endswitch.
     *
     * @return string
     */
    protected function compileEndswitch()
    {
        return '<?php endswitch; ?>';
    }

    /**
     * Penggunaan: @isset($var).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileIsset($value)
    {
        return "<?php if(isset{$value}): ?>";
    }

    /**
     * Penggunaan: @endisset.
     *
     * @return string
     */
    protected function compileEndisset()
    {
        return '<?php endif; ?>';
    }

    /**
     * Penggunaan: @continue or @continue($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileContinue($value)
    {
        if ($value) {
            $pattern = '/\(\s*(-?\d+)\s*\)$/';
            preg_match($pattern, $value, $matches);

            return $matches
                ? '<?php continue '.max(1, $matches[1]).'; ?>'
                : "<?php if{$value} continue; ?>";
        }

        return '<?php continue; ?>';
    }

    /**
     * Penggunaan: @exit or @exit($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileExit($value)
    {
        if ($value) {
            $pattern = '/\(\s*(-?\d+)\s*\)$/';
            preg_match($pattern, $value, $matches);

            return $matches
                ? '<?php exit '.max(1, $matches[1]).'; ?>'
                : "<?php if{$value} exit; ?>";
        }

        return '<?php exit; ?>';
    }

    /**
     * Penggunaan: @unless($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileUnless($value)
    {
        return "<?php if(! $value): ?>";
    }

    /**
     * Penggunaan: @endunless.
     *
     * @return string
     */
    protected function compileEndunless()
    {
        return '<?php endif; ?>';
    }

    /**
     * Penggunaan: @for($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileFor($value)
    {
        return "<?php for{$value}: ?>";
    }

    /**
     * Penggunaan: @endfor.
     *
     * @return string
     */
    protected function compileEndfor()
    {
        return '<?php endfor; ?>';
    }

    /**
     * Penggunaan: @foreach($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileForeach($value)
    {
        return "<?php foreach{$value}: ?>";
    }

    /**
     * Penggunaan: @endforeach.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileEndforeach()
    {
        return '<?php endforeach; ?>';
    }

    /**
     * Penggunaan: @forelse($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileForelse($value)
    {
        $this->emptyCounter++;

        return "<?php \$__empty_{$this->emptyCounter} = true; ".
            "foreach{$value}: ".
            "\$__empty_{$this->emptyCounter} = false;?>";
    }

    /**
     * Penggunaan: @empty.
     *
     * @return string
     */
    protected function compileEmpty()
    {
        $string = "<?php endforeach; if (\$__empty_{$this->emptyCounter}): ?>";
        $this->emptyCounter--;

        return $string;
    }

    /**
     * Penggunaan: @endforelse.
     *
     * @return string
     */
    protected function compileEndforelse()
    {
        return '<?php endif; ?>';
    }

    /**
     * Penggunaan: @while($condition).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileWhile($value)
    {
        return "<?php while{$value}: ?>";
    }

    /**
     * Penggunaan: @endwhile.
     *
     * @return string
     */
    protected function compileEndwhile()
    {
        return '<?php endwhile; ?>';
    }

    /**
     * Penggunaan: @extends($parentView).
     *
     * @param string $value
     *
     * @return string
     */
    protected function compileExtends($value)
    {
        if (isset($value) && '(' == $value[0]) {
            $value = substr($value, 1, -1);
        }

        return "<?php \$this->addParent({$value}) ?>";
    }

    /**
     * Penggunaan: @include($viewFile).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileInclude($value)
    {
        if (isset($value) && '(' == $value[0]) {
            $value = substr($value, 1, -1);
        }

        return "<?php include \$this->prepare({$value}) ?>";
    }

    /**
     * Penggunaan: @yield($data).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileYield($value)
    {
        return "<?php echo \$this->block{$value} ?>";
    }

    /**
     * Penggunaan: @section($view).
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileSection($value)
    {
        return "<?php \$this->beginBlock{$value} ?>";
    }

    /**
     * Penggunaan: @endsection.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileEndsection()
    {
        return '<?php $this->endBlock() ?>';
    }

    /**
     * Penggunaan: @show.
     *
     * @return string
     */
    protected function compileShow()
    {
        return '<?php echo $this->block($this->endBlock()) ?>';
    }

    /**
     * Penggunaan: @append.
     *
     * @return string
     */
    protected function compileAppend()
    {
        return '<?php $this->endBlock() ?>';
    }

    /**
     * Penggunaan: @stop.
     *
     * @return string
     */
    protected function compileStop()
    {
        return '<?php $this->endBlock() ?>';
    }

    /**
     * Penggunaan: @overwrite.
     *
     * @return string
     */
    protected function compileOverwrite()
    {
        return '<?php $this->endBlock(true) ?>';
    }

    /**
     * Penggunaan: @method('put').
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function compileMethod($value)
    {
        return '<input type="hidden" name="_method" '.
            "value=\"<?php echo strtoupper{$value} ?>\">\n";
    }

    //!----------------------------------------------------------------
    //! Renderer
    //!----------------------------------------------------------------

    /**
     * Compile view.
     *
     * @param string $name
     * @param array  $data
     * @param bool   $returnOnly
     *
     * @return string
     */
    public function make($name, array $data = [], $returnOnly = false)
    {
        // TODO: Tambahkan validation error message ke $data agar bisa diakses di view

        $html = $this->fetch($name, $data);

        if (is_cli()) {
            return json_encode($data);
        }

        if (false !== $returnOnly) {
            return $html;
        }

        echo $html;
    }

    /**
     * Bersihkan folder cache view.
     *
     * @return bool
     */
    public function clear()
    {
        $cache = Storage::glob($this->cacheDirectory.DS.'*.vc.php');

        return Storage::delete($cache);
    }

    /**
     * Set ekstensi file view.
     *
     * @param string $value
     */
    public function setFileExtension($value)
    {
        $this->fileExtension = $value;
    }

    /**
     * Set lokasi folder view.
     *
     * @param string $value
     */
    public function setViewDirectory($value)
    {
        $value = str_replace('/', DS, $value);
        $this->viewDirectory = $value;
    }

    /**
     * Set lokasi folder cache.
     *
     * @param string $value
     */
    public function setCacheDirectory($value)
    {
        $value = str_replace(['/', '\\'], [DS, DS], $value);
        $value = rtrim($value, DS).DS;

        $this->cacheDirectory = $value;
    }

    /**
     * Set format echo.
     *
     * @param string $value
     */
    public function setEchoFormat($value)
    {
        $this->echoFormat = $value;
    }

    /**
     * Tambahkan custom directive.
     *
     * @param callable $value
     */
    public function extend(callable $compiler)
    {
        $this->extensions[] = $compiler;
    }

    /**
     * Cara lain (yang lebih sederhana) untuk menambahkan custom directive.
     *
     * @param string $name
     * @param string $value
     */
    public function directive($name, callable $callback)
    {
        if (!preg_match('/^\w+(?:->\w+)?$/x', $name)) {
            $message = 'Invalid directive name ['.$name.']. Directive names '.
                'must only contains alphanumeric characters and underscores.';

            throw new InvalidArgumentException($message);
        }

        self::$directives[$name] = $callback;
    }

    /**
     * Ambil semua custom directive yang terdaftar.
     *
     * @return array
     */
    public function getAllDirectives()
    {
        return self::$directives;
    }

    /**
     * Cek apakah file view ada.
     *
     * @param string $view
     *
     * @return bool
     */
    public function exists($view)
    {
        $view = str_replace('.', '/', ltrim($view, '/'));
        $view = resource_path('views/'.$view.$this->fileExtension);

        return Storage::isFile($view);
    }

    /**
     * Siapkan file view (cari dan ekstrak datanya).
     *
     * @param string $name
     */
    protected function prepare($name)
    {
        $name = str_replace(['.', '/'], [DS, DS], ltrim($name, '/'));
        $tpl = $this->viewDirectory.$name.$this->fileExtension;
        $name = str_replace(['/', '\\', DS], ['.', '.', '.'], $name);
        $php = $this->cacheDirectory.$name.'__'.md5($name).'.vc.php';

        if (!Storage::isFile($php) || Storage::lastModified($tpl) > Storage::lastModified($php)) {
            if (!Storage::isFile($tpl)) {
                throw new RuntimeException('View file not found: '.$tpl);
            }

            $text = Storage::get($tpl);
            // tambahkan directive @set()
            $this->extend(function ($value) {
                return preg_replace(
                    "/@set\(['\"](.*?)['\"]\,(.*)\)/",
                    '<?php $$1 =$2; ?>',
                    $value
                );
            });

            $compilers = ['Statements', 'Comments', 'Echos', 'Extensions'];

            foreach ($compilers as $type) {
                $text = $this->{'compile'.$type}($text);
            }

            // replace blok @php dan @endphp
            $text = $this->replacePhpBlocks($text);
            Storage::put($php, $text);
        }

        return $php;
    }

    /**
     * Ambil data view yang dioper oleh user.
     *
     * @param string $name
     * @param array  $data
     */
    public function fetch($name, array $data = [])
    {
        $this->templates[] = $name;

        if (filled($data)) {
            extract($data);
        }

        while ($templates = array_shift($this->templates)) {
            $this->beginBlock('_view_data');
            require $this->prepare($templates);
            $this->endBlock(true);
        }

        return $this->block('_view_data');
    }

    /**
     * Method bantuan untuk @extends(), untuk mendefinisikan parent-view.
     *
     * @param string $name
     */
    protected function addParent($name)
    {
        $this->templates[] = $name;
    }

    /**
     * Return konten blok (jika ada).
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return string
     */
    protected function block($name, $default = '')
    {
        return array_key_exists($name, $this->blocks)
            ? $this->blocks[$name]
            : $default;
    }

    /**
     * Mulai blok view.
     *
     * @param string $name
     */
    protected function beginBlock($name)
    {
        array_push($this->blockStacks, $name);
        ob_start();
    }

    /**
     * Akhiri blok view.
     *
     * @param bool $overwrite
     */
    protected function endBlock($overwrite = false)
    {
        $name = array_pop($this->blockStacks);

        if ($overwrite || !array_key_exists($name, $this->blocks)) {
            $this->blocks[$name] = ob_get_clean();
        } else {
            $this->blocks[$name] .= ob_get_clean();
        }

        return $name;
    }

    protected function ensureDirectoryExists($directory)
    {
        if (!Storage::isDirectory($directory)) {
            Storage::makeDirectory($directory, 0777, true, true);
        }
    }
}
