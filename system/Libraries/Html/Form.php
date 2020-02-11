<?php

namespace System\Libraries\Html;

defined('DS') or exit('No direct script access allowed.');

class Form
{
    /**
     * Buat tag form pembuka.
     *
     * @param array $attr
     *
     * @return string
     */
    public function open(array $attr = [])
    {
        if (!is_array($attr)) {
            return;
        }

        if (!empty($attr)) {
            $form = '<form ';
            foreach ($attr as $key => $val) {
                $form .= $key.'="'.$val.'" ';
            }

            $form = trim($form).'>';
        } else {
            $form = '<form>';
        }

        return $form.PHP_EOL;
    }

    /**
     * Tutup tag form.
     *
     * @return string
     */
    public function close()
    {
        return '</form>'.PHP_EOL;
    }

    /**
     * Tambahkan tag label.
     *
     * @param string $for
     * @param string $text
     *
     * @return string
     */
    public function label($for, $text)
    {
        return '<label for="'.$for.'">'.$text.'</label>'.PHP_EOL;
    }

    /**
     * Tambahkan tag input text.
     *
     * @param string $name
     * @param array  $attr
     *
     * @return string
     */
    public function text($name, array $attr = [])
    {
        $input = '<input type="text" name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input = trim($input).'>';

        return $input.PHP_EOL;
    }

    /**
     * Tambahkan tag input password.
     *
     * @param string $name
     * @param array  $attr
     *
     * @return string
     */
    public function password($name, array $attr = [])
    {
        $input = '<input type="password" name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input = trim($input).'>';

        return $input.PHP_EOL;
    }

    /**
     * Tambahkan tag input email.
     *
     * @param string $name
     * @param array  $attr
     *
     * @return string
     */
    public function email($name, array $attr = [])
    {
        $input = '<input type="email" name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input = trim($input).'>';

        return $input.PHP_EOL;
    }

    /**
     * Tambahkan tag input hidden.
     *
     * @param string $name
     * @param array  $attr
     *
     * @return string
     */
    public function hidden($name, array $attr = [])
    {
        $input = '<input type="hidden" name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input = trim($input).'>';

        return $input.PHP_EOL;
    }

    /**
     * Tambahkan tag textarea.
     *
     * @param string $name
     * @param array  $attr
     *
     * @return string
     */
    public function textarea($name, array $attr = [])
    {
        $input = '<textarea name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                if ('content' != $key) {
                    $input .= $key.'="'.$val.'" ';
                }
            }
        }

        $input = trim($input).'>';

        if (array_key_exists('content', $attr)) {
            $input .= $attr['content'];
        }

        $input .= '</textarea>';

        return $input.PHP_EOL;
    }

    /**
     * Tambahkan tag select box.
     *
     * @param string $name
     * @param array  $options
     * @param string $selected
     * @param array  $attr
     *
     * @return string
     */
    public function select($name, array $options = [], $selected = null, array $attr = [])
    {
        $input = '<select name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input = trim($input).'>';

        $dropdown = '';
        if (!empty($options)) {
            foreach ($options as $key => $val) {
                if (!is_null($selected) && $selected === $key) {
                    $dropdown .= '<option value="'.$key.'" selected>'.$val.'</option>';
                } else {
                    $dropdown .= '<option value="'.$key.'">'.$val.'</option>';
                }
            }
        }

        return $input.PHP_EOL.$dropdown.PHP_EOL.'</select>'.PHP_EOL;
    }

    /**
     * Tambahkan tag multiple select box.
     *
     * @param string $name
     * @param array  $options
     * @param string $selected
     * @param array  $attr
     *
     * @return string
     */
    public function multiSelect($name, array $options = [], array $selected = [], array $attr = [])
    {
        $input = '<select name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input .= 'multiple="multiple">';

        $dropdown = '';
        if (!empty($options)) {
            foreach ($options as $key => $val) {
                if (!empty($selected)) {
                    if (in_array($key, $selected)) {
                        $dropdown .= '<option value="'.$key.'" selected>'.$val.'</option>';
                    } else {
                        $dropdown .= '<option value="'.$key.'">'.$val.'</option>';
                    }
                } else {
                    $dropdown .= '<option value="'.$key.'">'.$val.'</option>';
                }
            }
        }

        return $input.PHP_EOL.$dropdown.PHP_EOL.'</select>'.PHP_EOL;
    }

    /**
     * Tambahkan tag checkbox.
     *
     * @param string $name
     * @param string $value
     * @param bool   $checked
     * @param array  $attr
     *
     * @return string
     */
    public function checkbox($name, $value = '', $checked = false, array $attr = [])
    {
        $input = '<input type="checkbox" name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input .= 'value="'.$value.'" ';

        if ($checked) {
            $input .= 'checked';
        }

        $input = trim($input).'>';

        return $input.PHP_EOL;
    }

    /**
     * Tambahkan tag radio button.
     *
     * @param string $name
     * @param string $value
     * @param bool   $checked
     * @param array  $attr
     *
     * @return string
     */
    public function radio($name, $value = '', $checked = false, array $attr = [])
    {
        $input = '<input type="radio" name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input .= 'value="'.$value.'" ';

        if ($checked) {
            $input .= 'checked';
        }

        $input = trim($input).'>';

        return $input.PHP_EOL;
    }

    /**
     * Tambahkan tag input file (upload).
     *
     * @param string $name
     * @param bool   $multiple
     * @param array  $attr
     *
     * @return string
     */
    public function file($name, $multiple = false, array $attr = [])
    {
        $input = '<input type="file" ';

        if ($multiple) {
            $input .= 'name="'.$name.'[]" multiple="multiple" ';
        } else {
            $input .= 'name="'.$name.'" ';
        }

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input = trim($input).'>';

        return $input.PHP_EOL;
    }

    /**
     * Tambahkan tag tombol submit.
     *
     * @param string $name
     * @param string $value
     * @param array  $attr
     *
     * @return string
     */
    public function submit($name, $value = '', array $attr = [])
    {
        $input = '<input type="submit" name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input .= 'value="'.$value.'" ';
        $input = trim($input).'>';

        return $input.PHP_EOL;
    }

    /**
     * Tambahkan tag button.
     *
     * @param string $name
     * @param string $value
     * @param array  $attr
     *
     * @return string
     */
    public function button($name, $value = '', array $attr = [])
    {
        $input = '<button type="button" name="'.$name.'" ';

        if (!empty($attr)) {
            if (!array_key_exists('id', $attr)) {
                $input .= 'id="'.$name.'" ';
            }

            foreach ($attr as $key => $val) {
                $input .= $key.'="'.$val.'" ';
            }
        }

        $input = trim($input).'>'.$value.'</button>';

        return $input.PHP_EOL;
    }
}
