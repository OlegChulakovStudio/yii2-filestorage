<?php
/**
 * Файл класса PathService
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\services;

use yii\helpers\Url;
use yii\helpers\FileHelper;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\exceptions\NotFoundFileException;

/**
 * Class PathService
 * @package chulakov\filestorage\services
 */
class PathService
{
    /**
     * Storage path
     *
     * @var string
     */
    protected $storagePath;
    /**
     * Папка с сохраняемыми файлами
     *
     * @var string
     */
    protected $storageDir = 'upload';
    /**
     * Базовый url путь
     *
     * @var bool
     */
    protected $storageBaseUrl = false;
    /**
     * Если заданы права, то после создания файла они будут принудительно назначены
     *
     * @var number|null
     */
    public $fileMode = 0775;

    /**
     * Конструктор класса сервиса для работы с путями.
     *
     * @param string $path
     * @param string $storageDir
     * @param string $storageBaseUrl
     */
    public function __construct($path, $storageDir, $storageBaseUrl)
    {
        $this->storagePath = $path;
        $this->storageDir = $storageDir;
        $this->storageBaseUrl = $storageBaseUrl;
    }

    /**
     * Формирование относительного пути для сохранения файла
     *
     * @param PathParams $params
     * @param array $options
     * @return string
     */
    public function savedPath(PathParams $params, $options = [])
    {
        return $this->parsePattern($params->pathPattern, array_merge(
            array_filter($params->config()), $options
        ));
    }

    /**
     * Обновить path по паттерну
     *
     * @param string $path
     * @param PathParams $params
     * @return string
     */
    public function makePath($path, PathParams $params)
    {
        return $this->parsePattern($params->pathPattern,
            $this->parseConfig($path, $params)
        );
    }

    /**
     * Получить удаляемые файлы
     *
     * @param string $path
     * @param PathParams $params
     * @return array
     */
    public function searchAllFiles($path, PathParams $params)
    {
        $patternPath = $this->getAbsolutePath($this->parsePattern($params->searchPattern,
            $this->parseConfig($path, $params)
        ));
        return glob($patternPath, GLOB_BRACE & GLOB_ERR);
    }

    /**
     * Формирование параметров для парсинга из полного пути файла
     *
     * @param string $path
     * @param PathParams $params
     * @return array
     */
    public function parseConfig($path, PathParams $params)
    {
        $name = basename($path);
        $root = dirname($path);
        list($basename, $ext) = explode('.', $name, 2);

        $config = $this->filterConfig($params->config());
        $options = $this->filterConfig($params->options());
        $relay = str_replace($this->getAbsolute(), '', $root);

        return array_merge([
            '{relay}' => $relay,
            '{name}' => $name,
            '{basename}' => $basename,
            '{ext}' => $ext,
        ], $config, $options);
    }

    /**
     * Подстановка данных в паттерн
     *
     * @param string $pattern
     * @param array $config
     * @return string
     */
    public function parsePattern($pattern, $config)
    {
        $path = trim(strtr($pattern, $config));
        $path = str_replace(['\\', '\/'], DIRECTORY_SEPARATOR, $path);
        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Проверка пути
     *
     * @param string $file
     * @param string $uploadPath
     * @return null|string
     * @throws NotFoundFileException
     */
    public function findPath($file, $uploadPath)
    {
        if ($path = $this->checkSystemPath($file)) {
            return $path;
        }
        if ($path = $this->checkMovedPath($file, $uploadPath)) {
            return $path;
        }
        throw new NotFoundFileException('Не удалось найти файл :' . $file);
    }

    /**
     * Проверка path для получения url
     *
     * @param $file
     * @param $uploadPath
     * @param bool $isAbsolute
     * @return string
     * @throws NotFoundFileException
     */
    public function findUrl($file, $uploadPath, $isAbsolute = false)
    {
        return $this->convertToUrl($this->findPath($file, $uploadPath), $isAbsolute);
    }

    /**
     * Формирование абсолютного пути до файлов с созданием новой директории, если ее еще не существует
     *
     * @param string $path
     * @return string
     */
    public function getAbsolutePath($path)
    {
        return FileHelper::normalizePath(
            implode(DIRECTORY_SEPARATOR, [
                $this->getAbsolute(), $path,
            ])
        );
    }

    /**
     * Удаление файла
     *
     * @param string $path
     */
    public function removeFile($path)
    {
        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Добавление в URL адрес исходной точки
     *
     * @param string $path
     * @param bool $isAbsolute
     * @return string
     */
    public function convertToUrl($path, $isAbsolute = false)
    {
        $path = $this->cutPath($path);
        $url = '/' . $this->storageDir . '/' . trim(str_replace('\\', '/', $path), '/');
        if ($this->storageBaseUrl !== false) {
            $url = Url::to($this->storageBaseUrl . $url, true);
        } elseif ($isAbsolute) {
            $url = Url::base(true) . $url;
        }
        return $url;
    }

    /**
     * Получить сокращенный путь из абсолютного
     *
     * @param string $path
     * @return mixed
     */
    public function cutPath($path)
    {
        if (file_exists($path)) {
            return str_replace($this->getAbsolute(), '', $path);
        }
        return $path;
    }

    /**
     * Фильтрация конфигураций
     *
     * @param array $config
     * @return array
     */
    public function filterConfig($config)
    {
        return array_filter($config, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Проверка наличия директории с попыткой создать новую, если это возможно
     *
     * @param string $full
     * @return boolean
     * @throws \yii\base\Exception
     */
    protected function checkPath($full)
    {
        if (is_file($full)) {
            $full = dirname($full);
        }
        return is_dir($full) ? true : FileHelper::createDirectory($full, $this->fileMode);
    }

    /**
     * Проверка существования файла
     *
     * @param string $path
     * @return string|null
     */
    protected function checkExistFile($path)
    {
        return is_file($path) ? $path : null;
    }

    /**
     * Проверка системного расположения файла
     *
     * @param string $file
     * @return string
     */
    protected function checkSystemPath($file)
    {
        return $this->checkExistFile($this->getAbsolutePath($file));
    }

    /**
     * Проверка возможного перемещения файлов по новому шаблону
     *
     * @param string $file
     * @param string $uploadPath
     * @return string|null
     */
    protected function checkMovedPath($file, $uploadPath)
    {
        return $this->checkSystemPath(implode(DIRECTORY_SEPARATOR, [
            $uploadPath, basename($file)
        ]));
    }

    /**
     * Получить абсолютный путь
     *
     * @return string
     */
    protected function getAbsolute()
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->storagePath,
            $this->storageDir,
        ]);
    }
}