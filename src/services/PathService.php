<?php
/**
 * Файл класса PathService
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\services;

use chulakov\filestorage\params\PathParams;

class PathService
{
    protected $path;
    protected $category;

    public function __construct($path, $category)
    {
        $this->path = $path;
        $this->category = $category;
    }

    public function makePath($path, PathParams $params)
    {
        $path = $this->getPath($path);
        return $this->parsePattern(
            $path, $params->pathPattern, $params->config()
        );
    }

    public function getDeleteFiles($path, PathParams $params)
    {
        $path = $this->getPath($path);
        $pattern = $this->parsePattern(
            $path, $params->deletePattern, $params->config()
        );
        return glob($pattern, GLOB_BRACE & GLOB_ERR);
    }

    public function getPath($path)
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->path, $this->category, $path
        ]);
    }

    protected function parsePattern($path, $pattern, $config)
    {
        $name = basename($path);
        $path = dirname($path);

        list($basename, $ext) = explode('.', $name);

        return strtr($pattern, array_merge([
            '{root}' => $path,
            '{name}' => $name,
            '{basename}' => $basename,
            '{ext}' => $ext,
        ], $config));
    }
}
