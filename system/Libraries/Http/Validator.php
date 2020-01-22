<?php

namespace System\Libraries\Http;

defined('DS') or exit('No direct script access allowed.');

use Closure;
use Exception;
use System\Database\Connection;
use System\Database\Database;
use System\Facades\Request;
use System\Support\Messages;
use System\Support\Str;

class Validator
{
    public $attributes;
    public $errors;

    protected $rules = [];
    protected $messages = [];
    protected $db;
    protected $sizeRules = ['size', 'between', 'min', 'max'];
    protected $numericRules = ['numeric', 'integer'];

    protected static $validators = [];

    /**
     * Buat instance validator.
     *
     * @param mixed $attributes
     * @param array $rules
     * @param array $messages
     */
    public function __construct($attributes, $rules, $messages = [])
    {
        $rules = (array) $rules;

        foreach ($rules as $key => &$rule) {
            $rule = (is_string($rule)) ? explode('|', $rule) : $rule;
        }

        $this->rules = $rules;
        $this->messages = $messages;
        $this->attributes = (is_object($attributes))
            ? get_object_vars($attributes)
            : $attributes;
    }

    /**
     * Buat instance validator.
     *
     * @param array $attributes
     * @param array $rules
     * @param array $messages
     *
     * @return static
     */
    public static function make($attributes, $rules, $messages = [])
    {
        $messages = (array) $messages;

        return new static($attributes, $rules, $messages);
    }

    /**
     * Daftarkan validator baru.
     *
     * @param string   $name
     * @param \Closure $validator
     */
    public static function register($name, Closure $validator)
    {
        static::$validators[$name] = $validator;
    }

    /**
     * Cek apakah validasi berhasil.
     *
     * @return bool
     */
    public function passes()
    {
        return $this->valid();
    }

    /**
     * Cek apakah validasi gagal.
     *
     * @return bool
     */
    public function fails()
    {
        return $this->invalid();
    }

    /**
     * Cek apakah validasi gagal.
     *
     * @return bool
     */
    public function invalid()
    {
        return !$this->valid();
    }

    /**
     * Cek apakah validasi berhasil.
     *
     * @return bool
     */
    public function valid()
    {
        $this->errors = new Messages();
        foreach ($this->rules as $attribute => $rules) {
            foreach ($rules as $rule) {
                $this->check($attribute, $rule);
            }
        }

        return 0 == count($this->errors->messages);
    }

    /**
     * Evaluasi attribute berdasarkan rule validasi.
     *
     * @param string $attribute
     * @param string $rule
     */
    protected function check($attribute, $rule)
    {
        list($rule, $parameters) = $this->parse($rule);
        $value = array_get($this->attributes, $attribute);
        $validatable = $this->validatable($rule, $attribute, $value);

        $func = 'validate'.Str::studly($rule);

        if ($validatable && !$this->{$func}($attribute, $value, $parameters, $this)) {
            $this->error($attribute, $rule, $parameters);
        }
    }

    /**
     * Cek apakah attribute bisa divalidasi.
     * Untuk disebut 'bisa divalidasi', sebuah attribute harus ada dalam ruleset, atau
     * rule yang sedang divalidasi adalah required, required_with atau accepted.
     *
     * @param string $rule
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validatable($rule, $attribute, $value)
    {
        return $this->validateRequired($attribute, $value) || $this->implicit($rule);
    }

    /**
     * Cek apakah rule yang didefinisikan adalah salah satu dari
     * rule berikut: required, required_with atau accepted.
     *
     * @param string $rule
     *
     * @return bool
     */
    protected function implicit($rule)
    {
        return 'required' === $rule || 'accepted' === $rule || 'required_with' === $rule;
    }

    /**
     * Tambahkan sebuah pesan error.
     *
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     */
    protected function error($attribute, $rule, array $parameters)
    {
        $message = $this->message($attribute, $rule);
        $message = $this->replace($message, $attribute, $rule, $parameters);
        $this->errors->add($attribute, $message);
    }

    // --------------------------------------------------------------------
    // Rule Validasi
    // --------------------------------------------------------------------

    /**
     * Rule required.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateRequired($attribute, $value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && '' === trim($value)) {
            return false;
        } elseif (!is_null(Request::files($attribute, null))
        && is_array($value)
        && '' == $value['tmp_name']) {
            return false;
        }

        return true;
    }

    /**
     * Rule required_with.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateRequiredWith($attribute, $value, array $parameters)
    {
        $other = $parameters[0];
        $otherValue = array_get($this->attributes, $other);

        if ($this->validateRequired($other, $otherValue)) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }

    /**
     * Rule confirmed.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateConfirmed($attribute, $value)
    {
        return $this->validateSame($attribute, $value, [$attribute.'_confirmation']);
    }

    /**
     * Rule accepted.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateAccepted($attribute, $value)
    {
        return $this->validateRequired($attribute, $value)
            && ('yes' == $value || '1' == $value || 'on' == $value);
    }

    /**
     * Rule same.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateSame($attribute, $value, array $parameters)
    {
        $other = $parameters[0];

        return array_key_exists($other, $this->attributes)
            && $value == $this->attributes[$other];
    }

    /**
     * Rule different.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateDifferent($attribute, $value, array $parameters)
    {
        $other = $parameters[0];

        return array_key_exists($other, $this->attributes)
            && $value != $this->attributes[$other];
    }

    /**
     * Rule numeric.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateNumeric($attribute, $value)
    {
        return is_numeric($value);
    }

    /**
     * Rule integer.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateInteger($attribute, $value)
    {
        return false !== filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * Rule size.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateSize($attribute, $value, array $parameters)
    {
        return $this->size($attribute, $value) == $parameters[0];
    }

    /**
     * Rule between.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateBetween($attribute, $value, array $parameters)
    {
        $size = $this->size($attribute, $value);

        return $size >= $parameters[0] && $size <= $parameters[1];
    }

    /**
     * Rule min.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateMin($attribute, $value, array $parameters)
    {
        return $this->size($attribute, $value) >= $parameters[0];
    }

    /**
     * Rule max.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateMax($attribute, $value, array $parameters)
    {
        return $this->size($attribute, $value) <= $parameters[0];
    }

    /**
     * Ambil ukuran attribute.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function size($attribute, $value)
    {
        if (is_numeric($value) && $this->hasRule($attribute, $this->numericRules)) {
            return $this->attributes[$attribute];
        } elseif (array_key_exists($attribute, Request::files())) {
            return $value['size'] / 1024;
        }

        return Str::length(trim($value));
    }

    /**
     * Rule in.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateIn($attribute, $value, array $parameters)
    {
        return in_array($value, $parameters);
    }

    /**
     * Rule not_in.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateNotIn($attribute, $value, array $parameters)
    {
        return !in_array($value, $parameters);
    }

    /**
     * Rule unique.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateUnique($attribute, $value, array $parameters)
    {
        if (isset($parameters[1])) {
            $attribute = $parameters[1];
        }

        $query = $this->db()->table($parameters[0])->where($attribute, '=', $value);
        if (isset($parameters[2])) {
            $id = (isset($parameters[3])) ? $parameters[3] : 'id';
            $query->where($id, '<>', $parameters[2]);
        }

        return 0 == $query->count();
    }

    /**
     * Rule exists.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateExists($attribute, $value, array $parameters)
    {
        if (isset($parameters[1])) {
            $attribute = $parameters[1];
        }

        $count = (is_array($value)) ? count($value) : 1;
        $query = $this->db()->table($parameters[0]);

        if (is_array($value)) {
            $query = $query->whereIn($attribute, $value);
        } else {
            $query = $query->where($attribute, '=', $value);
        }

        return $query->count() >= $count;
    }

    /**
     * Rule ip.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateIp($attribute, $value)
    {
        return false !== filter_var($value, FILTER_VALIDATE_IP);
    }

    /**
     * Rule email.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateEmail($attribute, $value)
    {
        return false !== filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Rule url.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateUrl($attribute, $value)
    {
        return false !== filter_var($value, FILTER_VALIDATE_URL);
    }

    /**
     * Rule active_url.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateActiveUrl($attribute, $value)
    {
        $url = str_replace(['http://', 'https://', 'ftp://'], '', Str::lower($value));

        return ('' !== trim($url)) ? checkdnsrr($url) : false;
    }

    protected function validateMimes($attribute, $value, $parameters)
    {
        if (!is_array($value) || '' == array_get($value, 'tmp_name', '')) {
            return true;
        }

        foreach ($parameters as $extension) {
            if ($this->fileHasExtensions($extension, $value['tmp_name'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rule image.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateImage($attribute, $value)
    {
        return $this->validateMimes($attribute, $value, ['jpg', 'png', 'gif', 'bmp']);
    }

    /**
     * Rule alpha.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateAlpha($attribute, $value)
    {
        return preg_match('/^[\pL\pM]+$/u', $value);
    }

    /**
     * Rule alpha_num.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateAlphaNum($attribute, $value)
    {
        return preg_match('/^[\pL\pM\pN]+$/u', $value);
    }

    /**
     * Rule alpha_dash.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateAlphaDash($attribute, $value)
    {
        return preg_match('/^[\pL\pM\pN_-]+$/u', $value);
    }

    /**
     * Rule regex.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateRegex($attribute, $value, array $parameters)
    {
        return preg_match($parameters[0], $value);
    }

    /**
     * Rule array.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    protected function validateArray($attribute, $value)
    {
        return is_array($value);
    }

    /**
     * Rule count.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateCount($attribute, $value, array $parameters)
    {
        return is_array($value) && count($value) == $parameters[0];
    }

    /**
     * Rule count_min.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateCountMin($attribute, $value, array $parameters)
    {
        return is_array($value) && count($value) >= $parameters[0];
    }

    /**
     * Rule count_max.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateCountMax($attribute, $value, array $parameters)
    {
        return is_array($value) && count($value) <= $parameters[0];
    }

    /**
     * Rule count_between.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateCountBetween($attribute, $value, array $parameters)
    {
        return is_array($value)
            && count($value) >= $parameters[0]
            && count($value) <= $parameters[1];
    }

    /**
     * Rule before.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateBefore($attribute, $value, array $parameters)
    {
        return strtotime($value) < strtotime($parameters[0]);
    }

    /**
     * Rule after.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateAfter($attribute, $value, array $parameters)
    {
        return strtotime($value) > strtotime($parameters[0]);
    }

    /**
     * Rule date_format.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    protected function validateDateFormat($attribute, $value, array $parameters)
    {
        return false !== date_create_from_format($parameters[0], $value);
    }

    /**
     * Ambil pesan error untuk attribute dan rule.
     *
     * @param string $attribute
     * @param string $rule
     *
     * @return string
     */
    protected function message($attribute, $rule)
    {
        $custom = $attribute.'_'.$rule;
        if (array_key_exists($custom, $this->messages)) {
            return $this->messages[$custom];
        } elseif (array_key_exists($custom, lang('validator.custom'))) {
            return lang("validator.custom.{$custom}");
        } elseif (array_key_exists($rule, $this->messages)) {
            return $this->messages[$rule];
        } elseif (in_array($rule, $this->sizeRules)) {
            return $this->sizeMessage($attribute, $rule);
        }

        return lang("validator.{$rule}");
    }

    /**
     * Ambil pesan error untuk attribute dan rule size.
     *
     * @param string $attribute
     * @param string $rule
     *
     * @return string
     */
    protected function sizeMessage($attribute, $rule)
    {
        if ($this->hasRule($attribute, $this->numericRules)) {
            $line = 'numeric';
        } elseif (array_key_exists($attribute, Request::files())) {
            $line = 'file';
        } else {
            $line = 'string';
        }

        return lang("validator.{$rule}.{$line}");
    }

    /**
     * Replace placeholder di pesan error dengan nilai sebenarnya.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replace($message, $attribute, $rule, array $parameters)
    {
        $message = str_replace(':attribute', $this->attribute($attribute), $message);

        if (method_exists($this, $replacer = 'replace'.Str::studly($rule))) {
            $message = $this->{$replacer}($message, $attribute, $rule, $parameters);
        }

        return $message;
    }

    /**
     * Replace placeholder untuk rule required_with.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceRequiredWith($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':field', $this->attribute($parameters[0]), $message);
    }

    /**
     * Replace placeholder untuk rule between.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceBetween($message, $attribute, $rule, array $parameters)
    {
        return str_replace([':min', ':max'], $parameters, $message);
    }

    /**
     * Replace placeholder untuk rule size.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceSize($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':size', $parameters[0], $message);
    }

    /**
     * Replace placeholder untuk rule min.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceMin($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':min', $parameters[0], $message);
    }

    /**
     * Replace placeholder untuk rule max.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceMax($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':max', $parameters[0], $message);
    }

    /**
     * Replace placeholder untuk rule in.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceIn($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':values', implode(', ', $parameters), $message);
    }

    /**
     * Replace placeholder untuk rule not_in.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceNotIn($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':values', implode(', ', $parameters), $message);
    }

    /**
     * Replace placeholder untuk rule same.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceSame($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':other', $this->attribute($parameters[0]), $message);
    }

    /**
     * Replace placeholder untuk rule different.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceDifferent($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':other', $this->attribute($parameters[0]), $message);
    }

    /**
     * Replace placeholder untuk rule before.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceBefore($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':date', $parameters[0], $message);
    }

    /**
     * Replace placeholder untuk rule after.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceAfter($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':date', $parameters[0], $message);
    }

    /**
     * Replace placeholder untuk rule after.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceCount($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':count', $parameters[0], $message);
    }

    /**
     * Replace placeholder untuk rule count_min.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceCountMin($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':min', $parameters[0], $message);
    }

    /**
     * Replace placeholder untuk rule count_max.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceCountMax($message, $attribute, $rule, array $parameters)
    {
        return str_replace(':max', $parameters[0], $message);
    }

    /**
     * Replace placeholder untuk rule count_between.
     *
     * @param string $message
     * @param string $attribute
     * @param string $rule
     * @param array  $parameters
     *
     * @return string
     */
    protected function replaceCountBetween($message, $attribute, $rule, array $parameters)
    {
        return str_replace([':min', ':max'], $parameters, $message);
    }

    /**
     * Ambil nama alias attribute.
     *
     * @param string $attribute
     *
     * @return string
     */
    protected function attribute($attribute)
    {
        if (array_key_exists($attribute, lang('validator.attributes'))) {
            return lang("validator.attributes.{$attribute}");
        }

        return str_replace('_', ' ', $attribute);
    }

    /**
     * Cek apakah attribute sudah diberi rule atau belum.
     *
     * @param string $attribute
     * @param array  $rules
     *
     * @return bool
     */
    protected function hasRule($attribute, $rules)
    {
        foreach ($this->rules[$attribute] as $rule) {
            list($rule, $parameters) = $this->parse($rule);
            if (in_array($rule, $rules)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ekstrak nama rule dan parameter dari rule.
     *
     * @param string $rule
     *
     * @return array
     */
    protected function parse($rule)
    {
        $parameters = [];

        if (false !== ($colon = strpos($rule, ':'))) {
            $parameters = str_getcsv(substr($rule, $colon + 1));
        }

        return [is_numeric($colon) ? substr($rule, 0, $colon) : $rule, $parameters];
    }

    /**
     * Set koneksi database yang ingin digunakan untuk validator.
     *
     * @param \System\Database\Connection $connection
     *
     * @return $this
     */
    public function connection(Connection $connection)
    {
        $this->db = $connection;

        return $this;
    }

    /**
     * Ambil objek koneksi database.
     *
     * @return \System\Database\Database
     */
    protected function db()
    {
        if (!is_null($this->db)) {
            return $this->db;
        }

        return $this->db = Database::connection();
    }

    /**
     * Tangani pemanggilan method secara dinamis.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        $orig = $method;
        $method = substr($method, 8);
        $method = Str::studly($method);

        if (isset(static::$validators[$method])) {
            return call_user_func_array(static::$validators[$method], $parameters);
        }

        throw new Exception("Method [$orig] does not exist.");
    }

    /**
     * Determine if a file is of a given type.
     * The Fileinfo PHP extension is used to determine the file's MIME type.
     *
     * @param array|string $extensions
     * @param string       $path
     *
     * @return bool
     */
    protected static function fileHasExtensions($extensions, $path)
    {
        $extensions = (array) $extensions;
        $mimes = Config::get('mimes', []);
        $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);

        foreach ($extensions as $extension) {
            if (isset($mimes[$extension]) && in_array($mime, (array) $mimes[$extension])) {
                return true;
            }
        }

        return false;
    }
}
