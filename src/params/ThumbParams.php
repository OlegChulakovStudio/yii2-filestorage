<?php
/**
 * Файл класса ThumbParams
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\params;

/**
 * Class ThumbParams
 * @package chulakov\filestorage\params
 */
class ThumbParams extends ImageParams
{
    public $group = 'thumbs';
    /**
     * Шаблон сохранения thumbnails файлов
     *
     * @var string
     */
    protected $pathPattern = '{root}/{group}/{basename}/{width}x{height}.{ext}';

    /**
     * Получить путь файла относительно параметров
     *
     * @param string $path
     * @return string
     */
    public function getSavePath($path)
    {
        $basename = basename($path);
        $path = dirname($path);

        list($name, $ext) = explode('.', $basename);
        $ext = !empty($this->extension) ? $this->extension : $ext;

        return  strtr($this->pathPattern, [
            '{root}' => $path,
            '{group}' => $this->group,
            '{basename}' => $name,
            '{width}' => $this->width,
            '{height}' => $this->height,
            '{ext}' => $ext,
        ]);
    }
}