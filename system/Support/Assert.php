<?php

namespace System\Support;

defined('DS') or exit('No direct script access allowed.');

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Exception;
use InvalidArgumentException;
use Traversable;

class Assert
{
    /**
     * Assert string.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function string($value, $message = '')
    {
        if (!\is_string($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a string. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert non-empty string.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function stringNotEmpty($value, $message = '')
    {
        static::string($value, $message);
        static::notEq($value, '', $message);
    }

    /**
     * Assert integer.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function integer($value, $message = '')
    {
        if (!\is_int($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an integer. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert inetgerish.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function integerish($value, $message = '')
    {
        if (!\is_numeric($value) || $value != (int) $value) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an integerish value. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert float.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function float($value, $message = '')
    {
        if (!\is_float($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a float. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert numerik.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function numeric($value, $message = '')
    {
        if (!\is_numeric($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a numeric. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert integer non-negatif.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function natural($value, $message = '')
    {
        if (!\is_int($value) || $value < 0) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a non-negative integer. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert boolen.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function boolean($value, $message = '')
    {
        if (!\is_bool($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a boolean. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert scalar (integer, float, string atau boolean).
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function scalar($value, $message = '')
    {
        if (!\is_scalar($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a scalar. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert object.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function object($value, $message = '')
    {
        if (!\is_object($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an object. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert resource.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function resource($value, $type = null, $message = '')
    {
        if (!\is_resource($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a resource. Got: %s',
                static::typeToString($value)
            ));
        }

        if ($type && $type !== \get_resource_type($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a resource of type %2$s. Got: %s',
                static::typeToString($value),
                $type
            ));
        }
    }

    /**
     * Assert callable.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isCallable($value, $message = '')
    {
        if (!\is_callable($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a callable. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert array.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isArray($value, $message = '')
    {
        if (!\is_array($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an array. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert accessible array.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isArrayAccessible($value, $message = '')
    {
        if (!\is_array($value) && !($value instanceof ArrayAccess)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an array accessible. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert countable.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isCountable($value, $message = '')
    {
        $countable = (
            \is_array($value)
            || \is_object($value)
            || \is_iterable($value)
            || $value instanceof \Countable
            || $value instanceof \SimpleXMLElement
        );

        $result = $countable && (\class_exists('\ResourceBundle')
            ? ($countable instanceof \ResourceBundle) : $countable);

        if (true !== $result) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a countable. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert iterable.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isIterable($value, $message = '')
    {
        if (!\is_array($value) && !($value instanceof Traversable)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an iterable. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert instanceof.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isInstanceOf($value, $class, $message = '')
    {
        if (!($value instanceof $class)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an instance of %2$s. Got: %s',
                static::typeToString($value),
                $class
            ));
        }
    }

    /**
     * Assert not-instanceof.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function notInstanceOf($value, $class, $message = '')
    {
        if ($value instanceof $class) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an instance other than %2$s. Got: %s',
                static::typeToString($value),
                $class
            ));
        }
    }

    /**
     * Assert instanceof dari salah satu kelas yang diberikan.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isInstanceOfAny($value, array $classes, $message = '')
    {
        foreach ($classes as $class) {
            if ($value instanceof $class) {
                return;
            }
        }

        static::reportInvalidArgument(\sprintf(
            $message ? $message : 'Expected an instance of any of %2$s. Got: %s',
            static::typeToString($value),
            \implode(', ', \array_map(['static', 'valueToString'], $classes))
        ));
    }

    /**
     * Assert empty.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isEmpty($value, $message = '')
    {
        if (!empty($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an empty value. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert non-empty.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function notEmpty($value, $message = '')
    {
        if (empty($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a non-empty value. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert null.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function null($value, $message = '')
    {
        if (null !== $value) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected null. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert not-null.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function notNull($value, $message = '')
    {
        if (null === $value) {
            static::reportInvalidArgument(
                $message ? $message : 'Expected a value other than null.'
            );
        }
    }

    /**
     * Assert true.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function true($value, $message = '')
    {
        if (true !== $value) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to be true. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert false.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function false($value, $message = '')
    {
        if (false !== $value) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to be false. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert valid ip.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function ip($value, $message = '')
    {
        static::string($value, $message);
        if (false === \filter_var($value, \FILTER_VALIDATE_IP)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to be an IP. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert valid ipv4.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function ipv4($value, $message = '')
    {
        static::string($value, $message);
        if (false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to be an IPv4. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert valid ipv6.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function ipv6($value, $message = '')
    {
        static::string($value, $message);
        if (false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to be an IPv6. Got %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert valid email.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function email($value, $message = '')
    {
        static::string($value, $message);
        if (false === \filter_var($value, FILTER_VALIDATE_EMAIL)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to be a valid e-mail address. Got %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert unique value.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function uniqueValues(array $values, $message = '')
    {
        $allValues = \count($values);
        $uniqueValues = \count(\array_unique($values));

        if ($allValues !== $uniqueValues) {
            $difference = $allValues - $uniqueValues;

            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an array of unique values, but %s of them %s duplicated',
                $difference,
                (1 === $difference ? 'is' : 'are')
            ));
        }
    }

    /**
     * Assert equal.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function eq($value, $expect, $message = '')
    {
        if ($expect != $value) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value equal to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($expect)
            ));
        }
    }

    /**
     * Assert not equal.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function notEq($value, $expect, $message = '')
    {
        if ($expect == $value) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a different value than %s.',
                static::valueToString($expect)
            ));
        }
    }

    /**
     * Assert same (strict equal).
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function same($value, $expect, $message = '')
    {
        if ($expect !== $value) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value identical to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($expect)
            ));
        }
    }

    /**
     * Assert not same (strict not-equal).
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function notSame($value, $expect, $message = '')
    {
        if ($expect === $value) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value not identical to %s.',
                static::valueToString($expect)
            ));
        }
    }

    /**
     * Assert greater than.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function greaterThan($value, $limit, $message = '')
    {
        if ($value <= $limit) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value greater than %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ));
        }
    }

    /**
     * Assert greater than or equal to.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function greaterThanEq($value, $limit, $message = '')
    {
        if ($value < $limit) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value greater than or equal to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ));
        }
    }

    /**
     * Assert less than.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function lessThan($value, $limit, $message = '')
    {
        if ($value >= $limit) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value less than %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ));
        }
    }

    /**
     * Assert less than or equal to.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function lessThanEq($value, $limit, $message = '')
    {
        if ($value > $limit) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value less than or equal to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ));
        }
    }

    /**
     * Assert range.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function range($value, $min, $max, $message = '')
    {
        if ($value < $min || $value > $max) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value between %2$s and %3$s. Got: %s',
                static::valueToString($value),
                static::valueToString($min),
                static::valueToString($max)
            ));
        }
    }

    /**
     * Assert one of.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function oneOf($value, array $values, $message = '')
    {
        if (!\in_array($value, $values, true)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected one of: %2$s. Got: %s',
                static::valueToString($value),
                \implode(', ', \array_map(['static', 'valueToString'], $values))
            ));
        }
    }

    /**
     * Assert contains.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function contains($value, $subString, $message = '')
    {
        if (false === \strpos($value, $subString)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($subString)
            ));
        }
    }

    /**
     * Assert not contains.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function notContains($value, $subString, $message = '')
    {
        if (false !== \strpos($value, $subString)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : '%2$s was not expected to be contained in a value. Got: %s',
                static::valueToString($value),
                static::valueToString($subString)
            ));
        }
    }

    /**
     * Assert not whitespace only.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function notWhitespaceOnly($value, $message = '')
    {
        if (\preg_match('/^\s*$/', $value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a non-whitespace string. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert starts with.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function startsWith($value, $prefix, $message = '')
    {
        if (0 !== \strpos($value, $prefix)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to start with %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($prefix)
            ));
        }
    }

    /**
     * Assert starts with letter.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function startsWithLetter($value, $message = '')
    {
        static::string($value);

        $valid = isset($value[0]);

        if ($valid) {
            $locale = \setlocale(LC_CTYPE, 0);
            \setlocale(LC_CTYPE, 'C');
            $valid = \ctype_alpha($value[0]);
            \setlocale(LC_CTYPE, $locale);
        }

        if (!$valid) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to start with a letter. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert ends with.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function endsWith($value, $suffix, $message = '')
    {
        if ($suffix !== \substr($value, -\strlen($suffix))) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to end with %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($suffix)
            ));
        }
    }

    /**
     * Assert regex should match.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function regex($value, $pattern, $message = '')
    {
        if (!\preg_match($pattern, $value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'The value %s does not match the expected pattern.',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert regex should not match.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function notRegex($value, $pattern, $message = '')
    {
        if (\preg_match($pattern, $value, $matches, PREG_OFFSET_CAPTURE)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'The value %s matches the pattern %s (at offset %d).',
                static::valueToString($value),
                static::valueToString($pattern),
                $matches[0][1]
            ));
        }
    }

    /**
     * Assert unicode letters.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function unicodeLetters($value, $message = '')
    {
        static::string($value);

        if (!\preg_match('/^\p{L}+$/u', $value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain only Unicode letters. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert alphabetical.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function alpha($value, $message = '')
    {
        static::string($value);

        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_alpha($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain only letters. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert digits.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function digits($value, $message = '')
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_digit($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain digits only. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert alpha-numeric.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function alnum($value, $message = '')
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_alnum($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain letters and digits only. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert lowercase.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function lower($value, $message = '')
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_lower($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain lowercase characters only. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert uppercase.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function upper($value, $message = '')
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_upper($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain uppercase characters only. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert exact length.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function length($value, $length, $message = '')
    {
        if ($length !== static::strlen($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain %2$s characters. Got: %s',
                static::valueToString($value),
                $length
            ));
        }
    }

    /**
     * Assert minimum length.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function minLength($value, $min, $message = '')
    {
        if (static::strlen($value) < $min) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain at least %2$s characters. Got: %s',
                static::valueToString($value),
                $min
            ));
        }
    }

    /**
     * Assert maximum length.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function maxLength($value, $max, $message = '')
    {
        if (static::strlen($value) > $max) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain at most %2$s characters. Got: %s',
                static::valueToString($value),
                $max
            ));
        }
    }

    /**
     * Assert length between.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function lengthBetween($value, $min, $max, $message = '')
    {
        $length = static::strlen($value);

        if ($length < $min || $length > $max) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a value to contain between %2$s and %3$s characters. Got: %s',
                static::valueToString($value),
                $min,
                $max
            ));
        }
    }

    /**
     * Assert file exists.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function fileExists($value, $message = '')
    {
        static::string($value);

        if (!\file_exists($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'The file %s does not exist.',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert should be file.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function file($value, $message = '')
    {
        static::fileExists($value, $message);

        if (!\is_file($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'The path %s is not a file.',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert shoud be directory.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function directory($value, $message = '')
    {
        static::fileExists($value, $message);

        if (!\is_dir($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'The path %s is no directory.',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert readable path.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function readable($value, $message = '')
    {
        if (!\is_readable($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'The path %s is not readable.',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert writable path.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function writable($value, $message = '')
    {
        if (!\is_writable($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'The path %s is not writable.',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert class exists.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function classExists($value, $message = '')
    {
        if (!\class_exists($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an existing class name. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert subclass of.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function subclassOf($value, $class, $message = '')
    {
        if (!\is_subclass_of($value, $class)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected a sub-class of %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($class)
            ));
        }
    }

    /**
     * Assert interface exists.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function interfaceExists($value, $message = '')
    {
        if (!\interface_exists($value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an existing interface name. got %s',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert implements interface.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function implementsInterface($value, $interface, $message = '')
    {
        if (!\in_array($interface, \class_implements($value))) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an implementation of %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($interface)
            ));
        }
    }

    /**
     * Assert property should exists.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function propertyExists($classOrObject, $property, $message = '')
    {
        if (!\property_exists($classOrObject, $property)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected the property %s to exist.',
                static::valueToString($property)
            ));
        }
    }

    /**
     * Assert property should not exists.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function propertyNotExists($classOrObject, $property, $message = '')
    {
        if (\property_exists($classOrObject, $property)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected the property %s to not exist.',
                static::valueToString($property)
            ));
        }
    }

    /**
     * Assert method should exists.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function methodExists($classOrObject, $method, $message = '')
    {
        if (!\method_exists($classOrObject, $method)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected the method %s to exist.',
                static::valueToString($method)
            ));
        }
    }

    /**
     * Assert method should not exists.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function methodNotExists($classOrObject, $method, $message = '')
    {
        if (\method_exists($classOrObject, $method)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected the method %s to not exist.',
                static::valueToString($method)
            ));
        }
    }

    /**
     * Assert array key should exists.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function keyExists($array, $key, $message = '')
    {
        if (!(isset($array[$key]) || \array_key_exists($key, $array))) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected the key %s to exist.',
                static::valueToString($key)
            ));
        }
    }

    /**
     * Assert array key should not exists.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function keyNotExists($array, $key, $message = '')
    {
        if (isset($array[$key]) || \array_key_exists($key, $array)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected the key %s to not exist.',
                static::valueToString($key)
            ));
        }
    }

    /**
     * Assert valid array key.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function validArrayKey($value, $message = '')
    {
        if (!(\is_int($value) || \is_string($value))) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected string or integer. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    /**
     * Assert count.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function count($array, $number, $message = '')
    {
        static::eq(
            \count($array),
            $number,
            $message ? $message : \sprintf('Expected an array to contain %d elements. Got: %d.', $number, \count($array))
        );
    }

    /**
     * Assert minimum count.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function minCount($array, $min, $message = '')
    {
        if (\count($array) < $min) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an array to contain at least %2$d elements. Got: %d',
                \count($array),
                $min
            ));
        }
    }

    /**
     * Assert maximum count.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function maxCount($array, $max, $message = '')
    {
        if (\count($array) > $max) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an array to contain at most %2$d elements. Got: %d',
                \count($array),
                $max
            ));
        }
    }

    /**
     * Assert count between.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function countBetween($array, $min, $max, $message = '')
    {
        $count = \count($array);

        if ($count < $min || $count > $max) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Expected an array to contain between %2$d and %3$d elements. Got: %d',
                $count,
                $min,
                $max
            ));
        }
    }

    /**
     * Assert should be sequential array.
     *
     * @param mixed  $array
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isList($array, $message = '')
    {
        if (!\is_array($array) || !$array
        || \array_keys($array) !== \range(0, \count($array) - 1)) {
            static::reportInvalidArgument(
                $message ? $message : 'Expected list - non-associative array.'
            );
        }
    }

    /**
     * Assert should be associative array.
     *
     * @param mixed  $array
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function isMap($array, $message = '')
    {
        if (!\is_array($array) || !$array
            || \array_keys($array) !== \array_filter(\array_keys($array), function ($key) {
                return \is_string($key);
            })
        ) {
            static::reportInvalidArgument(
                $message ? $message : 'Expected map - associative array with string keys.'
            );
        }
    }

    /**
     * Assert valid uuid.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws \InvaliArgumentException
     */
    public static function uuid($value, $message = '')
    {
        $value = \str_replace(['urn:', 'uuid:', '{', '}'], '', $value);
        if ('00000000-0000-0000-0000-000000000000' === $value) {
            return;
        }

        $pattern = '/^[0-9A-Fa-f]{8}-'.
            '[0-9A-Fa-f]{4}-'.
            '[0-9A-Fa-f]{4}-'.
            '[0-9A-Fa-f]{4}-'.
            '[0-9A-Fa-f]{12}$/';

        if (!\preg_match($pattern, $value)) {
            static::reportInvalidArgument(\sprintf(
                $message ? $message : 'Value %s is not a valid UUID.',
                static::valueToString($value)
            ));
        }
    }

    /**
     * Assert should be throwing.
     *
     * @param \Closure $expression
     * @param string   $class
     * @param string   $message
     *
     * @throws \InvaliArgumentException
     */
    public static function throws(Closure $expression, $class = 'Exception', $message = '')
    {
        static::string($class);
        $actual = 'none';

        try {
            $expression();
        } catch (Exception $e) {
            $actual = \get_class($e);
            if ($e instanceof $class) {
                return;
            }
        } catch (\Throwable $e) {
            $actual = \get_class($e);
            if ($e instanceof $class) {
                return;
            }
        } catch (Exception $e) {
            $actual = \get_class($e);
            if ($e instanceof $class) {
                return;
            }
        }

        static::reportInvalidArgument($message ? $message : \sprintf(
            'Expected to throw "%s", got "%s"',
            $class,
            $actual
        ));
    }

    /**
     * Handle pemanggilan method statis.
     */
    public static function __callStatic($name, $arguments)
    {
        if ('nullOr' === \substr($name, 0, 6)) {
            if (null !== $arguments[0]) {
                $method = \lcfirst(\substr($name, 6));
                \call_user_func_array(['static', $method], $arguments);
            }

            return;
        }

        if ('all' === \substr($name, 0, 3)) {
            static::isIterable($arguments[0]);

            $method = \lcfirst(\substr($name, 3));
            $args = $arguments;

            foreach ($arguments[0] as $entry) {
                $args[0] = $entry;
                \call_user_func_array(['static', $method], $args);
            }

            return;
        }

        throw new BadMethodCallException('No such method: '.$name);
    }

    /**
     * Ubah segala value menjadi string.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected static function valueToString($value)
    {
        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (\is_array($value)) {
            return 'array';
        }

        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return \get_class($value).': '.self::valueToString($value->__toString());
            }

            return \get_class($value);
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        if (\is_string($value)) {
            return '"'.$value.'"';
        }

        return (string) $value;
    }

    /**
     * Ubah semua type menjadi string.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected static function typeToString($value)
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }

    /**
     * Helper strlen.
     *
     * @param string $value
     *
     * @return int
     */
    protected static function strlen($value)
    {
        if (!\function_exists('mb_detect_encoding')) {
            return \strlen($value);
        }

        if (false === $encoding = \mb_detect_encoding($value)) {
            return \strlen($value);
        }

        return \mb_strlen($value, $encoding);
    }

    /**
     * Throw InvalidArgumentException.
     *
     * @param string $message
     *
     * @return void
     */
    protected static function reportInvalidArgument($message)
    {
        throw new InvalidArgumentException($message);
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
    }
}
