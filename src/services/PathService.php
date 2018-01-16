<?php
/**
 * Файл класса PathService
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\services;

use Yii;
use Exception;
use yii\helpers\Url;
use yii\helpers\FileHelper;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\exceptions\NotFoundFileException;
use chulakov\filestorage\exceptions\NotUploadFileException;

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
    protected $storageDir = 'uploaded';
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
    public function __construct($path, $storageDir, $storageBaseUrl = '')
    {
        $this->storagePath = $path;
        $this->storageDir = $storageDir;

        if (!empty($storageBaseUrl)) {
            $this->storageBaseUrl = $storageBaseUrl;
        }
    }

    /**
     * Сгенерировать путь
     *
     * @param string $path
     * @param PathParams $params
     * @return string
     */
    public function makePath($path, PathParams $params)
    {
        $path = $this->getPath($path);
        return $this->updatePath(
            $path, $params->pathPattern, $params->config()
        );
    }

    /**
     * Получить удаляемые файлы
     *
     * @param string $path
     * @param PathParams $params
     * @return array
     */
    public function getDeleteFiles($path, PathParams $params)
    {
        $path = $this->getPath($path);
        $patternPath = $this->parsePattern(
            $params->deletePattern, $params->getConfigWithPath($path)
        );
        return glob($patternPath, GLOB_BRACE & GLOB_ERR);
    }

    /**
     * Получить составной путь
     *
     * @param string $path
     * @return string
     */
    public function getPath($path)
    {
        return implode(DIRECTORY_SEPARATOR, [
            $this->storagePath, $this->storageDir, $path
        ]);
    }

    /**
     * Обновить path по паттерну
     *
     * @param string $path
     * @param string $pattern
     * @param array $config
     * @return string
     */
    public function updatePath($path, $pattern, $config)
    {
        $name = basename($path);
        $path = dirname($path);

        list($basename, $ext) = explode('.', $name);

        return $this->parsePattern($pattern, array_merge(
            [
                '{root}' => $path,
                '{name}' => $name,
                '{basename}' => $basename,
                '{ext}' => $ext,
            ],
            $config
        ));
    }

    /**
     * Подстановка данных в  паттерн
     *
     * @param string $pattern
     * @param array $config
     * @return string
     */
    public function parsePattern($pattern, $config)
    {
        return trim(strtr($pattern, $config));
    }


    /**
     * Получить сохраняемый путь через параметры
     *
     * @param PathParams $params
     * @return string
     */
    public function getSavePathFromParams(PathParams $params)
    {
        return $this->parsePattern($params->pathPattern, $params->config());
    }

    /**
     * Проверка существования файла
     *
     * @param string $path
     * @return string|null
     */
    protected function checkExistFile($path)
    {
        $path = FileHelper::normalizePath($path);
        if (is_file($path)) {
            return $path;
        }
        return null;
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
        return $this->checkExistFile(implode(DIRECTORY_SEPARATOR, [
            $uploadPath, basename($file)
        ]));
    }

    /**
     * Проверка системного расположения файла
     *
     * @param string $file
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    protected function checkSystemPath($file)
    {
        return $this->checkExistFile(implode(DIRECTORY_SEPARATOR, [
            Yii::getAlias($this->storagePath), $file
        ]));
    }

    /**
     * Проверка path
     *
     * @param string $file
     * @param string $uploadPath
     * @return null|string
     * @throws \yii\base\InvalidParamException
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
        throw new NotFoundFileException('Не удалось найти файл :' . basename($file));
    }

    /**
     * Проверка path для получения url
     *
     * @param $file
     * @param $uploadPath
     * @param bool $isAbsolute
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function findUrl($file, $uploadPath, $isAbsolute = false)
    {
        if ($this->checkSystemPath($file)) {
            return $this->convertToUrl($file, $isAbsolute);
        }
        $baseName = '/' . basename($file);
        if ($this->checkMovedPath($file, $uploadPath)) {
            return $uploadPath . $baseName;
        }
        return $this->convertToUrl($baseName, $isAbsolute);
    }

    /**
     * Формирование абсолютного пути до файлов с созданием новой директории, если ее еще не существует
     *
     * @param string $path
     * @return string
     *
     * @throws \yii\base\InvalidParamException
     * @throws NotUploadFileException
     */
    public function getAbsolutePath($path)
    {
        $full = FileHelper::normalizePath(
            implode(
                DIRECTORY_SEPARATOR,
                [
                    Yii::getAlias($this->storagePath),
                    $this->storageDir,
                    $path,
                ])
        );

        if (!$this->checkPath($full)) {
            throw new NotUploadFileException('Нет доступа к каталогу для сохранения файла.');
        }
        return $full;
    }

    /**
     * Проверка наличия директории с попыткой создать новую, если это возможно
     *
     * @param string $full
     * @return boolean
     */
    protected function checkPath($full)
    {
        if (!is_dir($full)) {
            try {
                return FileHelper::createDirectory($full, $this->fileMode);
            } catch (Exception $e) {
                return false;
            }
        }
        return true;
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
     * @throws \yii\base\InvalidParamException
     */
    public function convertToUrl($path, $isAbsolute = false)
    {
        $url = '/' . trim(str_replace('\\', '/', $path), '/');
        if ($this->storageBaseUrl !== false) {
            $url = Url::to($this->storageBaseUrl . $url, true);
        } elseif ($isAbsolute) {
            $url = Url::base(true) . $url;
        }
        return $url;
    }

    /**
     * Возвращает абсолютный путь к директории хранения файлов определенного типа
     *
     * @param string $savePath
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function getUploadPath($savePath)
    {
        $path = FileHelper::normalizePath(implode(DIRECTORY_SEPARATOR, [
            Yii::getAlias($this->storagePath), $savePath
        ]));
        return $path;
    }
}
