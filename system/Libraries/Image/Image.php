<?php

namespace System\Libraries\Image;

defined('DS') or exit('No direct script access allowed.');

use System\Support\Messages;

class Image
{
    private $file;

    private $image;

    private $tempImage;

    private $width;

    private $height;

    private $newWidth;

    private $newHeight;

    private $htmlSize;

    private $format;

    private $extension;

    private $size;

    private $basename;

    private $dirname;

    private $error = '';

    private $cropCoordinates;

    private $rgb = [255, 255, 255];

    private $quality = 100;

    private $imageFormats = ['jpeg' => 2, 'jpg' => 2, 'gif' => 1, 'png' => 3];

    /**
     * Load file gambar.
     *
     * @param string $file
     *
     * @return $this|false
     */
    public function load($file)
    {
        if (!($this->error instanceof Messages)) {
            $this->error = new Messages();
        }

        $this->file = public_path($file);
        $this->extractImageInfo();

        if (blank($this->error->messages)) {
            return $this;
        }

        return false;
    }

    /**
     * Load gambar dari url.
     *
     * @param string $url
     *
     * @return $this|false
     */
    public function loadUrl($url)
    {
        if (!($this->error instanceof Messages)) {
            $this->error = new Messages();
        }

        $this->file = $url;
        $this->extractFileInfo();

        if (!$this->format) {
            $this->error->add('url', lang('image.url'));

            return false;
        }

        $this->createImage();
        $this->updateDimensions();

        return $this;
    }

    /**
     * Ekstrak info gambar.
     */
    private function extractImageInfo()
    {
        if (is_file($this->file)) {
            $this->extractFileInfo();
            if (!$this->isImage()) {
                $this->error->add('filetype', sprintf(lang('image.filetype', $this->file)));
            } else {
                $this->createImage();
            }
        } else {
            $this->error->add('notfound', lang('image.notfound'));
        }
    }

    /**
     * Ambil dimensi gambar.
     *
     * @return $this
     */
    private function getDimensions()
    {
        list(
            $this->width,
            $this->height,
            $this->htmlSize,
            $this->format
        ) = getimagesize($this->file);

        return $this;
    }

    /**
     * Update property dimensi gambar.
     *
     * @return $this
     */
    private function updateDimensions()
    {
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);

        return $this;
    }

    /**
     * Ekstrak info file.
     */
    private function extractFileInfo()
    {
        $pathinfo = pathinfo($this->file);
        $this->extractFileMimeType();

        $this->basename = $pathinfo['basename'];
        $this->dirname = $pathinfo['dirname'];
        $this->format = (
            isset($this->imageFormats[$this->extension])
            ? $this->imageFormats[$this->extension]
            : null
        );
    }

    /**
     * Ekstrak mime type gambar.
     */
    private function extractFileMimeType()
    {
        $size = getimagesize($this->file);
        $mimeType = $size['mime'];

        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
        ];

        if (isset($mimeTypes[$mimeType])) {
            $this->extension = $mimeTypes[$mimeType];
        } else {
            $this->error->add('mime', lang('image.mime'));
        }
    }

    /**
     * Cek apakah file merupakan gambar (berdasarkan formatnya).
     *
     * @return bool
     */
    private function isImage()
    {
        $this->getDimensions();

        return $this->format ? true : false;
    }

    /**
     * Buat gambar kosong.
     *
     * @param int    $width
     * @param int    $height
     * @param string $extension
     * @param bool   $alpha
     *
     * @return $this|false
     */
    public function createEmptyImage($width, $height, $extension = 'jpg', $alpha = false)
    {
        if (!$width || !$height) {
            return false;
        }

        $this->width = $width;
        $this->height = $height;
        $this->image = imagecreatetruecolor($this->width, $this->height);

        if ($alpha) {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);
            $backgroundColor = imagecolorallocatealpha(
                $this->image,
                $this->rgb[0],
                $this->rgb[1],
                $this->rgb[2],
                $alpha
            );
        } else {
            $backgroundColor = imagecolorallocate(
                $this->image,
                $this->rgb[0],
                $this->rgb[1],
                $this->rgb[2]
            );
        }

        imagefill($this->image, 0, 0, $backgroundColor);
        $this->extension = $extension;

        return $this;
    }

    /**
     * Buat gambar.
     *
     * @return $this
     */
    private function createImage()
    {
        $extension = ('jpg' == $this->extension) ? 'jpeg' : $this->extension;
        $function = "imagecreatefrom{$extension}";

        if (function_exists($function)) {
            $this->image = $function($this->file);
        } else {
            $this->error->add('function', lang('image.function'));
        }

        return $this;
    }

    /**
     * Set warna background gambar.
     *
     * @param array $rgb
     *
     * @return $this|false
     */
    public function setBackgroundColor($rgb)
    {
        if (is_array($rgb)) {
            $this->rgb = $rgb;

            return $this;
        }

        if ($this->convertHexToRgb($rgb)) {
            return $this;
        }

        return false;
    }

    /**
     * Ubah hexadecimal color ke rgb.
     *
     * @param string $hexColor
     *
     * @return $this|false
     */
    private function convertHexToRgb($hexColor)
    {
        $hexColor = str_replace('#', '', $hexColor);

        if (3 == strlen($hexColor)) {
            $hexColor .= $hexColor;
        }

        if (6 != strlen($hexColor)) {
            return false;
        }

        $this->rgb = [
            hexdec(substr($hexColor, 0, 2)),
            hexdec(substr($hexColor, 2, 2)),
            hexdec(substr($hexColor, 4, 2)),
        ];

        return $this;
    }

    /**
     * Set koordinat untuk cropping.
     *
     * @param int $x
     * @param int $y
     *
     * @return $this
     */
    public function setCropCoordinates($x, $y)
    {
        $this->cropCoordinates = [$x, $y, $this->width, $this->height];

        return $this;
    }

    /**
     * Resize gambar.
     *
     * @param int    $newWidth
     * @param int    $newHeight
     * @param string $method    Value yang tersedia: null, crop, fill
     *
     * @return $this|false
     */
    public function resize($newWidth = null, $newHeight = null, $method = null)
    {
        if (!$newWidth && !$newHeight) {
            $this->error->add('new_dim', lang('image.new_dim'));

            return false;
        } elseif (!is_resource($this->image)) {
            return false;
        }

        $this->newWidth = $newWidth;
        $this->newHeight = $newHeight;

        $this->calculateNewDimensions();

        if ($method) {
            $method = 'resizeWith'.Str::studly($method);
        }

        if (!method_exists($this, $method)) {
            $method = 'resizeWithNoMethod';
        }

        $this->$method()->updateDimensions();

        return $this;
    }

    /**
     * Hitung dimensi baru (setelah cropping).
     */
    private function calculateNewDimensions()
    {
        $this->checkForPercentages();
        if (!$this->newWidth) {
            $this->newWidth = $this->width / ($this->height / $this->newHeight);
        } elseif (!$this->newHeight) {
            $this->newHeight = $this->height / ($this->width / $this->newWidth);
        }
    }

    /**
     * Hitung presentase width dan height baru (setelah cropping).
     */
    private function checkForPercentages()
    {
        if (strpos($this->newWidth, '%')) {
            $newWidth = preg_replace('/[^0-9]/', '', $this->newWidth) / 100;
            $this->newWidth = round($this->width * $newWidth);
        }

        if (strpos($this->newHeight, '%')) {
            $newHeight = $newWidth = preg_replace('/[^0-9]/', '', $this->newHeight) / 100;
            $this->newHeight = round($this->height * $newHeight);
        }
    }

    /**
     * Resize standar, tanpa perubahan.
     *
     * @return $this
     */
    private function resizeWithNoMethod()
    {
        $this->tempImage = imagecreatetruecolor($this->newWidth, $this->newHeight);
        imagecopyresampled(
            $this->tempImage,
            $this->image,
            0,
            0,
            0,
            0,
            $this->newWidth,
            $this->newHeight,
            $this->width,
            $this->height
        );

        $this->image = $this->tempImage;

        return $this;
    }

    /**
     * Helper untuk resize fill.
     */
    private function fill()
    {
        imagefill(
            $this->tempImage,
            0,
            0,
            imagecolorallocate(
                $this->tempImage,
                $this->rgb[0],
                $this->rgb[1],
                $this->rgb[2]
            )
        );
    }

    /**
     * Resize dengan fill.
     *
     * @return $this
     */
    private function resizeWithFill()
    {
        $this->tempImage = imagecreatetruecolor($this->newWidth, $this->newHeight);
        $this->fill();

        if (($this->width / $this->height) >= ($this->newWidth / $this->newHeight)) {
            $diffW = $this->newWidth;
            $diffH = $this->height * ($this->newWidth / $this->width);
            $diffX = 0;
            $diffY = round(($this->newHeight - $diffH) / 2);
        } else {
            $diffW = $this->width * ($this->newHeight / $this->height);
            $diffH = $this->newHeight;
            $diffX = round(($this->newWidth - $diffW) / 2);
            $diffY = 0;
        }

        imagecopyresampled(
            $this->tempImage,
            $this->image,
            $diffX,
            $diffY,
            0,
            0,
            $diffW,
            $diffH,
            $this->width,
            $this->height
        );

        $this->image = $this->tempImage;

        return $this;
    }

    /**
     * Resize dengan cropping.
     *
     * @return $this
     */
    private function resizeWithCrop()
    {
        if (!is_array($this->cropCoordinates)) {
            $this->cropCoordinates = [0, 0, $this->width, $this->height];
        }

        $this->tempImage = imagecreatetruecolor($this->newWidth, $this->newHeight);
        $this->fill();
        imagecopyresampled(
            $this->tempImage,
            $this->image,
            $this->cropCoordinates[0],
            $this->cropCoordinates[1],
            0,
            0,
            $this->cropCoordinates[2],
            $this->cropCoordinates[3],
            $this->width,
            $this->height
        );

        $this->image = $this->tempImage;

        return $this;
    }

    /**
     * Flip gambar.
     *
     * @param string $orientation Value yang tersedia: vertical, horizontal
     *
     * @return $this|false
     */
    public function flip($orientation = 'horizontal')
    {
        $orientation = strtolower($orientation);

        if ('horizontal' != $orientation && 'vertical' != $orientation) {
            return false;
        }

        $w = imagesx($this->image);
        $h = imagesy($this->image);

        $this->tempImage = imagecreatetruecolor($w, $h);
        $method = 'flip'.Str::studly($orientation);
        $this->$method($w, $h);
        $this->image = $this->tempImage;

        return $this;
    }

    /**
     * Flip horizontal.
     *
     * @param int $w
     * @param int $h
     */
    private function flipHorizontal($w, $h)
    {
        for ($x = 0; $x < $w; ++$x) {
            imagecopy(
                $this->tempImage,
                $this->image,
                $x,
                0,
                ($w - $x - 1),
                0,
                1,
                $h
            );
        }
    }

    /**
     * Flip vertical.
     *
     * @param int $w
     * @param int $h
     */
    private function flipVertical($w, $h)
    {
        for ($y = 0; $y < $h; ++$y) {
            imagecopy(
                $this->tempImage,
                $this->image,
                0,
                $y,
                0,
                ($h - $y - 1),
                $w,
                1
            );
        }
    }

    /**
     * Putar gambar.
     *
     * @param int $degrees
     */
    public function rotate($degrees)
    {
        $backgroundColor = imagecolorallocate(
            $this->image,
            $this->rgb[0],
            $this->rgb[1],
            $this->rgb[2]
        );

        $this->image = imagerotate($this->image, $degrees, $backgroundColor);
        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);
        $this->updateDimensions();

        return $this;
    }

    /**
     * Tambahkan teks ke gambar.
     *
     * @param string $text
     * @param array  $options
     *
     * @return $this|false
     */
    public function text($text, array $options = [])
    {
        if (!$text) {
            return false;
        }

        if (!isset($options['size'])) {
            $options['size'] = 5;
        }

        if (isset($options['color'])) {
            $this->setBackgroundColor($options['color']);
        }

        $textColor = imagecolorallocate(
            $this->image,
            $this->rgb[0],
            $this->rgb[1],
            $this->rgb[2]
        );

        $dimensions = $this->textDimensions($text, $options);

        $options['x'] = isset($options['x']) ? $options['x'] : 0;
        $options['y'] = isset($options['y']) ? $options['y'] : 0;

        if (is_string($options['x']) && is_string($options['y'])) {
            list($options['x'], $options['y']) = $this->calculateTextPosition(
                $options['x'],
                $options['y'],
                $dimensions['width'],
                $dimensions['height']
            );
        }

        if (isset($options['background_color']) && $options['background_color']) {
            $this->textBackgroundColor($dimensions, $options);
        }

        if (isset($options['truetype']) && $options['truetype']) {
            $this->addTrueTypeText($text, $textColor, $options);
        } else {
            imagestring(
                $this->image,
                $options['size'],
                $options['x'],
                $options['y'],
                $text,
                $textColor
            );
        }

        return $this;
    }

    /**
     * Atur dimensi teks.
     *
     * @param string $text
     * @param array  $options
     *
     * @return $this|false
     */
    private function textDimensions($text, array $options = [])
    {
        if (isset($options['truetype']) && $options['truetype']) {
            $textDimensions = imagettfbbox($options['size'], 0, $options['font'], $text);

            return [$textDimensions[4], $options['size']];
        }

        if ($options['size'] > 5) {
            $options['size'] = 5;
        }

        return [
            'width' => imagefontwidth($options['size']) * strlen($text),
            'height' => imagefontheight($options['size']),
        ];
    }

    /**
     * Hitung posisi teks.
     *
     * @param int $x      Value yang tersedia: left, right, center
     * @param int $y      Value yang tersedia: top, bottom, middle
     * @param int $width
     * @param int $height
     *
     * @return array
     */
    private function calculateTextPosition($x, $y, $width, $height)
    {
        switch ($y) {
            case 'top':
            default:       $y = 0;

break;
            case 'bottom': $y = $this->height - $height;

break;
            case 'middle':
                switch ($x) {
                    case 'left':
                    case 'right':  $y = ($this->height / 2) - ($height / 2);

break;
                    case 'center': $y = ($this->height - $height) / 2;

break;
                }

                break;
        }

        switch ($x) {
            case 'left':
            default:       $x = 0;

break;
            case 'center': $x = ($this->width - $width) / 2;

break;
            case 'right':  $x = $this->width - $width;

break;
        }

        return [$x, $y];
    }

    /**
     * Set warna background teks.
     *
     * @param array $dimensions
     * @param array $options
     */
    private function textBackgroundColor($dimensions, array $options)
    {
        $this->setBackgroundColor($options['background_color']);
        $this->tempImage = imagecreatetruecolor($dimensions['width'], $dimensions['height']);
        $backgroundColor = imagecolorallocate(
            $this->tempImage,
            $this->rgb[0],
            $this->rgb[1],
            $this->rgb[2]
        );

        imagefill($this->tempImage, 0, 0, $backgroundColor);
        imagecopy(
            $this->image,
            $this->tempImage,
            $options['x'],
            $options['y'],
            0,
            0,
            $dimensions['width'],
            $dimensions['height']
        );
    }

    /**
     * Tambahkan teks true type.
     *
     * @param string $text
     * @param string $textColor
     * @param array  $options
     *
     * @return $this|false
     */
    private function addTrueTypeText($text, $textColor, $options)
    {
        imagettftext(
            $this->image,
            $options['size'],
            0,
            $options['x'],
            ($options['y'] + $options['size']),
            $textColor,
            $options['font'],
            $text
        );
    }

    /**
     * Gabungkan gambar.
     *
     * @param string $image
     * @param array  $position
     * @param int    $alpha
     *
     * @return $this|false
     */
    public function merge($image, $position, $alpha = 100)
    {
        if (!file_exists($image)) {
            $this->error = lang('image.general');

            return false;
        }

        list($w, $h) = getimagesize($image);

        if (is_string($position[0]) && is_string($position[1])) {
            $position = $this->calculateTextPosition($position[0], $position[1], $w, $h);
        }

        $pathinfo = pathinfo($image);
        $extension = strtolower($pathinfo['extension']);
        $extension = ('jpg' == $extension ? 'jpeg' : $extension);
        $function = 'imagecreatefrom'.$extension;

        if (function_exists($function)) {
            $imageToMerge = $function($image);
        } else {
            $this->error->add('file', lang('image.file'));
        }

        list($x, $y) = $position;

        if (is_numeric($alpha) && (($alpha > 0) && ($alpha < 100))) {
            imagecopymerge($this->image, $imageToMerge, $x, $y, 0, 0, $w, $h, $alpha);
        } else {
            imagecopy($this->image, $this->imageToMerge, $x, $y, 0, 0, $w, $h);
        }

        return $this;
    }

    /**
     * Set kualitas gambar.
     *
     * @param int $quality
     *
     * @return $this
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Simpan hasil render gambar ke file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function save($path)
    {
        if (!is_dir(dirname(public_path($path)))) {
            $this->error->add('destination', lang('image.destination'));

            return false;
        }

        return $this->renderOutput($path);
    }

    /**
     * Tampilkan hasil rennder gambar (tanpa menyimpan).
     */
    public function show()
    {
        if (headers_sent()) {
            $this->error->add('headers', lang('image.headers'));

            return false;
        }

        header("Content-type: image/{$this->extension}");
        $this->renderOutput();
        imagedestroy($this->image);

        exit;
    }

    /**
     * Render gambar.
     *
     * @param string $path
     */
    private function renderOutput($path = null)
    {
        $pathinfo = pathinfo($path);
        $extension = (isset($pathinfo['extension']))
            ? strtolower($pathinfo['extension'])
            : $this->extension;

        if ('jpg' == $extension || 'jpeg' == $extension) {
            imagejpeg($this->image, $path, $this->quality);
        } elseif ('png' == $extension) {
            imagepng($this->image, $path);
        } elseif ('gif' == $extension) {
            imagegif($this->image, $path);
        } else {
            $this->reset();

            return false;
        }

        $this->reset();
    }

    /**
     * Reset property kelas.
     */
    private function reset()
    {
        foreach (get_class_vars(get_class($this)) as $name => $default) {
            $this->$name = $default;
        }
    }

    /**
     * Ambil pesan error operasi gambar.
     *
     * @return \System\Support\Messages
     */
    public function errors()
    {
        return $this->error;
    }
}
