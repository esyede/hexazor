<?php

namespace System\Libraries\Image;

defined('DS') or exit('No direct script access allowed.');

use Exception;
use System\Core\Application;

class Image
{
    const POS_LEFT = 1;
    const POS_CENTER = 2;
    const POS_RIGHT = 4;
    const POS_TOP = 8;
    const POS_MIDDLE = 16;
    const POS_BOTTOM = 32;

    protected $storage;
    protected $file;
    protected $data;
    protected $history = false;
    protected $count = 0;
    protected $key;

    /**
     * Buat instance baru.
     */
    public function __construct()
    {
        $this->storage = storage_path('system/image/');

        if (!is_dir($this->storage)) {
            if (false === @mkdir($this->storage, 0755, true)) {
                throw new Exception('Unable to create temporary folder: '.$this->storage);
            }
        }
    }

    /**
     * Mulai operasi image.
     *
     * @param  string $file
     * @param  bool   $history
     * @param  string $path
     *
     * @return string
     */
    public function make($file = null, $history = false, $path = null)
    {
        $this->history = $history;
        if ($file) {
            $this->file = $file;

            if (!isset($path)) {
                $path = $this->storage;
            }
            
            foreach ((array) $path as $dir) {
                if (is_file($dir.$file)) {
                    return $this->load(file_get_contents($dir.$file));
                }
            }

            throw new Exception('File not found: '.$file);
        }
    }

    /**
     * Konversi hex RGB menjadi array.
     *
     * @param  int|string $color
     *
     * @return array|false
     */
    public function rgb($color)
    {
        if (is_string($color)) {
            $color = hexdec($color);
        }

        $hex = str_pad($hex = dechex($color), $color < 4096 ? 3 : 6, '0', STR_PAD_LEFT);

        if (($length = strlen($hex)) > 6) {
            throw new Exception('Invalid color specified: 0x'.$hex);
        }

        $color = str_split($hex, $length / 3);

        foreach ($color as &$hue) {
            $hue = hexdec(str_repeat($hue, 6 / $length));
            unset($hue);
        }

        return $color;
    }

    /**
     * Invert image.
     *
     * @return $this
     */
    public function invert()
    {
        imagefilter($this->data, IMG_FILTER_NEGATE);

        return $this->save();
    }

    /**
     * Atur brightness (level: -255 sampai 255).
     *
     * @param  int $level
     *
     * @return $this
     */
    public function brightness($level)
    {
        imagefilter($this->data, IMG_FILTER_BRIGHTNESS, $level);

        return $this->save();
    }

    /**
     * Atur kontras image (level: -100 sampai 100).
     *
     * @param  int $level
     *
     * @return $this
     */
    public function contrast($level)
    {
        imagefilter($this->data, IMG_FILTER_CONTRAST, $level);

        return $this->save();
    }

    /**
     * Konversi image ke grayscale.
     *
     * @return $this
     */
    public function grayscale()
    {
        imagefilter($this->data, IMG_FILTER_GRAYSCALE);

        return $this->save();
    }

    /**
     * Atur kelembutan image.
     *
     * @param  int $level
     *
     * @return $this
     */
    public function smoothness($level)
    {
        imagefilter($this->data, IMG_FILTER_SMOOTH, $level);

        return $this->save();
    }

    /**
     * Tambahkan efek emboss.
     *
     * @return $this
     */
    public function emboss()
    {
        imagefilter($this->data, IMG_FILTER_EMBOSS);

        return $this->save();
    }

    /**
     * Tambahkan efek sepia.
     *
     * @return $this
     */
    public function sepia()
    {
        imagefilter($this->data, IMG_FILTER_GRAYSCALE);
        imagefilter($this->data, IMG_FILTER_COLORIZE, 90, 60, 45);

        return $this->save();
    }

    /**
     * Tambahkan efek pixelate.
     *
     * @param  int $size
     *
     * @return $this
     */
    public function pixelate($size)
    {
        imagefilter($this->data, IMG_FILTER_PIXELATE, $size, true);

        return $this->save();
    }

    /**
     * Tambahkan efek blur.
     *
     * @param  bool $selective
     *
     * @return $this
     */
    public function blur($selective = false)
    {
        imagefilter(
            $this->data,
            $selective ? IMG_FILTER_SELECTIVE_BLUR : IMG_FILTER_GAUSSIAN_BLUR
        );

        return $this->save();
    }

    /**
     * Tambahkan efek sketch.
     *
     * @return $this
     */
    public function sketch()
    {
        imagefilter($this->data, IMG_FILTER_MEAN_REMOVAL);

        return $this->save();
    }

    /**
     * Flip horizontal.
     *
     * @return $this
     */
    public function hflip()
    {
        $temp = imagecreatetruecolor(
            $width = $this->width(),
            $height = $this->height()
        );
        
        imagesavealpha($temp, true);
        imagefill($temp, 0, 0, IMG_COLOR_TRANSPARENT);
        imagecopyresampled(
            $temp,
            $this->data,
            0,
            0,
            $width - 1,
            0,
            $width,
            $height,
            - $width,
            $height
        );

        imagedestroy($this->data);
        $this->data = $temp;

        return $this->save();
    }

    /**
     * Flip vertikal.
     *
     * @return $this
     */
    public function vflip()
    {
        $temp = imagecreatetruecolor(
            $width = $this->width(),
            $height = $this->height()
        );
    
        imagesavealpha($temp, true);
        imagefill($temp, 0, 0, IMG_COLOR_TRANSPARENT);
        imagecopyresampled(
            $temp,
            $this->data,
            0,
            0,
            0,
            $height - 1,
            $width,
            $height,
            $width,
            - $height
        );

        imagedestroy($this->data);
        $this->data = $temp;

        return $this->save();
    }

    /**
     * Crop image.
     *
     * @param int $x1
     * @param int $y1
     * @param int $x2
     * @param int $y2
     *
     * @return $this
     */
    public function crop($x1, $y1, $x2, $y2)
    {
        $temp = imagecreatetruecolor($width = $x2 - $x1 + 1, $height = $y2 - $y1 + 1);

        imagesavealpha($temp, true);
        imagefill($temp, 0, 0, IMG_COLOR_TRANSPARENT);
        imagecopyresampled(
            $temp,
            $this->data,
            0,
            0,
            $x1,
            $y1,
            $width,
            $height,
            $width,
            $height
        );

        imagedestroy($this->data);
        $this->data = $temp;
        
        return $this->save();
    }

    /**
     * Ubah ukuran image.
     *
     * @param  int  $width
     * @param  int  $height
     * @param  bool $crop
     * @param  bool $enlarge
     *
     * @return $this
     */
    public function resize($width = null, $height = null, $crop = true, $enlarge = true)
    {
        if (is_null($width) && is_null($height)) {
            return $this;
        }

        $originalWidth = $this->width();
        $originalHeight = $this->height();
        
        if (is_null($width)) {
            $width = round(($height / $originalHeight) * $originalWidth);
        }
        
        if (is_null($height)) {
            $height = round(($width / $originalWidth) * $originalHeight);
        }

        $ratio = $originalWidth / $originalHeight;
        
        if (!$crop) {
            if ($width / $ratio <= $height) {
                $height = round($width / $ratio);
            } else {
                $width = round($height * $ratio);
            }
        }

        if (!$enlarge) {
            $width = min($originalWidth, $width);
            $height = min($originalHeight, $height);
        }

        $temp = imagecreatetruecolor($width, $height);
        imagesavealpha($temp, true);
        imagefill($temp, 0, 0, IMG_COLOR_TRANSPARENT);

        if ($crop) {
            if ($width / $ratio <= $height) {
                $cropWidth = round($originalHeight * $width / $height);
                imagecopyresampled(
                    $temp,
                    $this->data,
                    0,
                    0,
                    ($originalWidth - $cropWidth) / 2,
                    0,
                    $width,
                    $height,
                    $cropWidth,
                    $originalHeight
                );
            } else {
                $cropHeight = round($originalWidth * $height / $width);
                imagecopyresampled(
                    $temp,
                    $this->data,
                    0,
                    0,
                    0,
                    ($originalHeight - $cropHeight) / 2,
                    $width,
                    $height,
                    $originalWidth,
                    $cropHeight
                );
            }
        } else {
            imagecopyresampled(
                $temp,
                $this->data,
                0,
                0,
                0,
                0,
                $width,
                $height,
                $originalWidth,
                $originalHeight
            );
        }

        imagedestroy($this->data);
        $this->data = $temp;
        
        return $this->save();
    }

    /**
     * Putar image.
     *
     * @param  int $angle
     *
     * @return $this
     */
    public function rotate($angle)
    {
        $this->data = imagerotate(
            $this->data,
            $angle,
            imagecolorallocatealpha($this->data, 0, 0, 0, 127)
        );
        
        imagesavealpha($this->data, true);

        return $this->save();
    }


    public function overlay(Image $img, $align = null, $alpha = 100)
    {
        if (is_null($align)) {
            $align = self::POS_RIGHT | self::POS_BOTTOM;
        }

        if (is_array($align)) {
            list($posX, $posY) = $align;
            $align  =  0;
        }

        $overlay = imagecreatefromstring($img->dump());
        imagesavealpha($overlay, true);
        
        $imgWidth = $this->width();
        $imgHeight = $this->height();
        $overlayWidth = imagesx($overlay);
        $overlayHeight = imagesy($overlay);
        
        if ($align & self::POS_LEFT) {
            $posX = 0;
        }
        
        if ($align & self::POS_CENTER) {
            $posX = ($imgWidth - $overlayWidth) / 2;
        }

        if ($align & self::POS_RIGHT) {
            $posX = $imgWidth - $overlayWidth;
        }
        
        if ($align & self::POS_TOP) {
            $posY = 0;
        }
        
        if ($align & self::POS_MIDDLE) {
            $posY = ($imgHeight - $overlayHeight) / 2;
        }
        
        if ($align & self::POS_BOTTOM) {
            $posY = $imgHeight - $overlayHeight;
        }

        if (empty($posX)) {
            $posX = 0;
        }

        if (empty($posY)) {
            $posY = 0;
        }
        
        if ($alpha === 100) {
            imagecopy($this->data, $overlay, $posX, $posY, 0, 0, $overlayWidth, $overlayHeight);
        } else {
            $cut = imagecreatetruecolor($overlayWidth, $overlayHeight);
            imagecopy($cut, $this->data, 0, 0, $posX, $posY, $overlayWidth, $overlayHeight);
            imagecopy($cut, $overlay, 0, 0, 0, 0, $overlayWidth, $overlayHeight);
            imagecopymerge(
                $this->data,
                $cut,
                $posX,
                $posY,
                0,
                0,
                $overlayWidth,
                $overlayHeight,
                $alpha
            );
        }

        return $this->save();
    }

    /**
     * Buat identicon image.
     *
     * @param  string $str
     * @param  int    $size
     * @param  int    $blocks
     *
     * @return $this
     */
    public function identicon($str, $size = 64, $blocks = 4)
    {
        $sprites = $this->sprites();

        $hash = sha1($str);
        $this->data = imagecreatetruecolor($size, $size);
        list($r, $g, $b) = $this->rgb(hexdec(substr($hash, -3)));
        
        $color = imagecolorallocate($this->data, $r, $g, $b);
        imagefill($this->data, 0, 0, IMG_COLOR_TRANSPARENT);
        
        $ctr = count($sprites);
        $dimension = $blocks * floor($size / $blocks) * 2 / $blocks;
        
        for ($j = 0, $y = ceil($blocks / 2); $j < $y; $j++) {
            for ($i = $j, $x = $blocks - 1 - $j; $i < $x; $i++) {
                $sprite = imagecreatetruecolor($dimension, $dimension);
                imagefill($sprite, 0, 0, IMG_COLOR_TRANSPARENT);
                $block = $sprites[hexdec($hash[($j * $blocks + $i) * 2]) % $ctr];
                
                for ($k = 0, $points = count($block); $k < $points; $k++) {
                    $block[$k] *= $dimension;
                }

                imagefilledpolygon($sprite, $block, $points / 2, $color);
                
                for ($k = 0; $k < 4; $k++) {
                    imagecopyresampled(
                        $this->data,
                        $sprite,
                        $i * $dimension / 2,
                        $j * $dimension / 2,
                        0,
                        0,
                        $dimension / 2,
                        $dimension / 2,
                        $dimension,
                        $dimension
                    );

                    $this->data = imagerotate(
                        $this->data,
                        90,
                        imagecolorallocatealpha($this->data, 0, 0, 0, 127)
                    );
                }

                imagedestroy($sprite);
            }
        }

        imagesavealpha($this->data, true);
        
        return $this->save();
    }

    /**
     * Buat captcha image.
     *
     * @param  string $font
     * @param  int    $size
     * @param  int    $length
     * @param  string $path
     * @param  int    $color
     * @param  int    $bg
     *
     * @return $this
     */
    public function captcha($font, $size = 24, $length = 5, $path = '', $color = 0xFFFFFF, $bg = 0x000000)
    {
        if ($length < 4 || $length > 13) {
            throw new Exception('Invalid captcha length: '.$length);
        }

        if (!function_exists('imagettftext')) {
            throw new Exception('No truetype support in GD module.');
        }
        
        $dirs = rtrim(base_path($path), DS).DS;

        foreach ($dirs as $dir) {
            if (is_file($path = base_path(rtrim(rtrim($dir, '/'), DS).'/'.$font))) {
                $seed = strtoupper(substr(bin2hex(random_bytes($length)), - $length));
                $block = $size * 3;
                $temp = [];
                
                for ($i = 0, $width = 0, $height = 0; $i < $length; $i++) {
                    $box = imagettfbbox($size * 2, 0, $path, $seed[$i]);
                    $w = $box[2] - $box[0];
                    $h = $box[1] - $box[5];
                    $char = imagecreatetruecolor($block, $block);
                    
                    imagefill($char, 0, 0, $bg);
                    imagettftext(
                        $char,
                        $size * 2,
                        0,
                        ($block - $w) / 2,
                        $block-($block - $h) / 2,
                        $color,
                        $path,
                        $seed[$i]
                    );

                    $char = imagerotate(
                        $char,
                        mt_rand(- 30, 30),
                        imagecolorallocatealpha($char, 0, 0, 0, 127)
                    );

                    $temp[$i] = imagecreatetruecolor(
                        ($w = imagesx($char)) / 2,
                        ($h = imagesy($char)) / 2
                    );

                    imagefill($temp[$i], 0, 0, IMG_COLOR_TRANSPARENT);
                    imagecopyresampled(
                        $temp[$i],
                        $char,
                        0,
                        0,
                        0,
                        0,
                        $w / 2,
                        $h / 2,
                        $w,
                        $h
                    );

                    imagedestroy($char);
                    $width += ($i + 1 < $length) ? ($block / 2) : ($w / 2);
                    $height = max($height, $h / 2);
                }

                $this->data = imagecreatetruecolor($width, $height);
                imagefill($this->data, 0, 0, IMG_COLOR_TRANSPARENT);
                
                for ($i = 0; $i < $length; $i++) {
                    imagecopy(
                        $this->data,
                        $temp[$i],
                        $i * $block / 2,
                        ($height - imagesy($temp[$i])) / 2,
                        0,
                        0,
                        imagesx($temp[$i]),
                        imagesy($temp[$i])
                    );

                    imagedestroy($temp[$i]);
                }

                imagesavealpha($this->data, true);

                if (is_null($key)) {
                    $this->key = $seed;
                }
                
                return $this->save();
            }
        }

        throw new Exception('Captcha font not found: '.$font);
    }

    /**
     * Ambil info lebar image.
     *
     * @return int
     */
    public function width()
    {
        return imagesx($this->data);
    }

    /**
     * Ambil info tinggi image.
     *
     * @return int
     */
    public function height()
    {
        return imagesy($this->data);
    }

    /**
     * Cetak image ke browser.
     *
     * @return void
     */
    public function render(/* ...$options */)
    {
        $options = func_get_args();
        $format = $options ? array_shift($options) : 'png';
        $format = strtolower($format);

        if (!in_array($format, ['png', 'jpeg', 'gif', 'wbmp'])) {
            $format = 'png';
        }
        
        if (!is_cli()) {
            header('Content-Type: image/'.$format);
            header('X-Powered-By: '.Application::PACKAGE);
        }

        call_user_func_array('image'.$format, array_merge([$this->data, null], $options));
    }

    /**
     * Return image sebagai string.
     *
     * @return string
     */
    public function dump(/* ...$options */)
    {
        $options = func_get_args();
        $format = $options ? array_shift($options) : 'png';
        $format = strtolower($format);

        if (!in_array($format, ['png', 'jpeg', 'gif', 'wbmp'])) {
            $format = 'png';
        }

        ob_start();
        call_user_func_array('image'.$format, array_merge([$this->data,null], $options));
        
        return ob_get_clean();
    }

    /**
     * Mereturn data resource image
     *
     * @return resource
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Simpan perubahan saat ini.
     *
     * @return $this
     */
    public function save()
    {
        if ($this->history) {
            $this->count++;
            file_put_contents(
                $this->storage.uniqid().'.'.md5($this->file).'-'.$this->count.'.png',
                $this->dump()
            );
        }

        return $this;
    }

    /**
     * Rollback ke step sebelumnya.
     *
     * @param  int $step
     *
     * @return $this
     */
    public function rollback($step = 1)
    {
        if ($this->history && is_file($file = ($path = $this->storage.
            uniqid().'.'.md5($this->file).'-').$step.'.png')) {
            if (is_resource($this->data)) {
                imagedestroy($this->data);
            }
          
            $this->data = imagecreatefromstring(file_get_contents($file));
            imagesavealpha($this->data, true);
          
            foreach (glob($path.'*.png', GLOB_NOSORT) as $match) {
                if (preg_match('/-(\d+)\.png/', $match, $parts) && $parts[1] > $step) {
                    @unlink($match);
                }
            }
          
            $this->count = $step;
        }

        return $this;
    }

    /**
     * Rollback ke satu step sebelumnya.
     *
     * @return $this
     */
    public function undo()
    {
        if ($this->history) {
            if ($this->count) {
                $this->count--;
            }

            return $this->restore($this->count);
        }

        return $this;
    }

    /**
     * Load string image.
     *
     * @param  string $str
     *
     * @return $this
     */
    public function load($str)
    {
        if (!$this->data = @imagecreatefromstring($str)) {
            return false;
        }

        imagesavealpha($this->data, true);
        $this->save();
        
        return $this;
    }

    /**
     * Sprite data untuk identicon.
     *
     * @return array
     */
    protected function sprites()
    {
        return [
            [.5, 1, 1, 0, 1, 1],
            [.5, 0, 1, 0, .5, 1, 0, 1],
            [.5, 0, 1, 0, 1, 1, .5, 1, 1, .5],
            [0, .5, .5, 0, 1, .5, .5, 1, .5, .5],
            [0, .5, 1, 0, 1, 1, 0, 1, 1, .5],
            [1, 0, 1, 1, .5, 1, 1, .5, .5, .5],
            [0, 0, 1, 0, 1, .5, 0, 0, .5, 1, 0, 1],
            [0, 0, .5, 0, 1, .5, .5, 1, 0, 1, .5, .5],
            [.5, 0, .5, .5, 1, .5, 1, 1, .5, 1, .5, .5, 0, .5],
            [0, 0, 1, 0, .5, .5, 1, .5, .5, 1, .5, .5, 0, 1],
            [0, .5, .5, 1, 1, .5, .5, 0, 1, 0, 1, 1, 0, 1],
            [.5, 0, 1, 0, 1, 1, .5, 1, 1, .75, .5, .5, 1, .25],
            [0, .5, .5, 0, .5, .5, 1, 0, 1, .5, .5, 1, .5, .5, 0, 1],
            [0, 0, 1, 0, 1, 1, 0, 1, 1, .5, .5, .25, .5, .75, 0, .5, .5, .25],
            [0, .5, .5, .5, .5, 0, 1, 0, .5, .5, 1, .5, .5, 1, .5, .5, 0, 1],
            [0, 0, 1, 0, .5, .5, .5, 0, 0, .5, 1, .5, .5, 1, .5, .5, 0, 1],
        ];
    }

    /**
     * Reset / destruct objek image.
     *
     * @return void
     */
    public function reset()
    {
        if (is_resource($this->data)) {
            imagedestroy($this->data);
            $path = $this->storage.uniqid().'.'.md5($this->file);
         
            if ($glob = @glob($path.'*.png', GLOB_NOSORT)) {
                foreach ($glob as $match) {
                    if (preg_match('/-(\d+)\.png/', $match)) {
                        @unlink($match);
                    }
                }
            }
        }
    }

    /**
     * Destruct.
     */
    public function __destruct()
    {
        $this->reset();
    }
}
