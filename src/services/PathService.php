<?php
/**
 * Файл класса PathService
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\services;

use chulakov\filestorage\exceptions\NotFoundFileException;
use chulakov\filestorage\params\PathParams;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\helpers\Url;

/**
 * Сервис управления путями хранения файлов
 *
 * @package chulakov\filestorage\services
 */
class PathService
{
    /**
     * Конструктор класса сервиса для работы с путями.
     *
     * @param string $storagePath Storage path
     * @param string $storageDir Папка с сохраняемыми файлами
     * @param string|false $storageBaseUrl Базовый url путь
     * @param int|null $fileMode Если заданы права, то после создания файла они будут принудительно назначены
     */
    public function __construct(
        protected string $storagePath,
        protected string $storageDir,
        protected string|false $storageBaseUrl,
        protected ?int $fileMode = 0o775,
    ) {}

    /**
     * Формирование относительного пути для сохранения файла
     */
    public function savedPath(PathParams $params, array $extra = []): string
    {
        $config = $this->filterConfig($params->config());
        $options = $this->filterConfig($params->options());

        return $this->parsePattern($params->pathPattern, array_merge($config, $options, $extra));
    }

    /**
     * Обновить path по паттерну
     */
    public function makePath(string $path, PathParams $params): string
    {
        return $this->parsePattern($params->pathPattern, $this->parseConfig($path, $params));
    }

    /**
     * Получить удаляемые файлы
     * @return string[]
     */
    public function searchAllFiles(string $path, PathParams $params): array
    {
        $patternPath = $this->getAbsolutePath(
            $this->parsePattern($params->searchPattern, $this->parseConfig($path, $params)),
        );

        return glob($patternPath);
    }

    /**
     * Формирование параметров для парсинга из полного пути файла
     */
    public function parseConfig(string $path, PathParams $params): array
    {
        $name = basename($path);
        $root = dirname($path);
        [$basename, $ext] = explode('.', $name, 2);

        $config = $this->filterConfig($params->config());
        $options = $this->filterConfig($params->options());
        $relay = $this->cutPath($root);

        return array_merge([
            '{relay}' => $relay,
            '{name}' => $name,
            '{basename}' => $basename,
            '{ext}' => $ext,
        ], $config, $options);
    }

    /**
     * Подстановка данных в паттерн
     */
    public function parsePattern(string $pattern, array $config): string
    {
        $path = trim(strtr($pattern, $config));
        $path = $this->convertSlashes($path);
        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Проверка пути
     * @throws NotFoundFileException
     */
    public function findPath(string $file, string $uploadPath): ?string
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
     * @throws NotFoundFileException
     */
    public function findUrl(string $file, string $uploadPath, bool $isAbsolute = false): string
    {
        return $this->convertToUrl($this->findPath($file, $uploadPath), $isAbsolute);
    }

    /**
     * Формирование абсолютного пути до файлов с созданием новой директории, если ее еще не существует
     */
    public function getAbsolutePath(string $path): string
    {
        return FileHelper::normalizePath(
            implode(DIRECTORY_SEPARATOR, [$this->getAbsolute(), $path]),
        );
    }

    /**
     * Удаление файла/директории
     */
    public function removeFile(string $path): bool
    {
        if (is_file($path)) {
            return unlink($path);
        }
        if (is_dir($path)) {
            FileHelper::removeDirectory($path);
            return true;
        }

        return false;
    }

    /**
     * Добавление в URL адрес исходной точки
     */
    public function convertToUrl(string $path, bool $isAbsolute = false): string
    {
        $path = $this->cutPath($path);
        $url = $this->convertSlashes(implode('/', [$this->storageDir, trim($path, '/')]), '/');
        if ($this->storageBaseUrl !== false) {
            $url = Url::to($this->storageBaseUrl . '/' . $url, true);
        } elseif ($isAbsolute) {
            $url = Url::base(true) . '/' . $url;
        }
        return $url;
    }

    /**
     * Получить сокращенный путь из абсолютного
     */
    public function cutPath(string $path): string
    {
        return str_replace($this->getAbsolute(), '', $this->convertSlashes($path));
    }

    /**
     * Фильтрация конфигураций
     */
    public function filterConfig(array $config): array
    {
        return array_filter($config, static fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Проверка наличия директории с попыткой создать новую, если это возможно
     * @throws Exception
     */
    public function checkPath(string $full): bool
    {
        if (is_file($full)) {
            $full = dirname($full);
        }
        if (!is_dir($full)) {
            return FileHelper::createDirectory($full, $this->fileMode);
        }
        return true;
    }

    /**
     * Проверка существования файла
     */
    protected function checkExistFile(string $path): ?string
    {
        return is_file($path) ? $path : null;
    }

    /**
     * Проверка системного расположения файла
     */
    protected function checkSystemPath(string $file): ?string
    {
        return $this->checkExistFile($this->getAbsolutePath($file));
    }

    /**
     * Проверка возможного перемещения файлов по новому шаблону
     */
    protected function checkMovedPath(string $file, string $uploadPath): ?string
    {
        return $this->checkSystemPath(implode(DIRECTORY_SEPARATOR, [$uploadPath, basename($file)]));
    }

    /**
     * Получить абсолютный путь
     */
    protected function getAbsolute(): string
    {
        return $this->convertSlashes(
            implode(DIRECTORY_SEPARATOR, [$this->storagePath, $this->storageDir]),
        );
    }

    /**
     * Конвертация пути в общий вид для ОС
     */
    protected function convertSlashes(string $path, string $slash = DIRECTORY_SEPARATOR): string
    {
        return str_replace(['\\', '\/'], $slash, $path);
    }
}
