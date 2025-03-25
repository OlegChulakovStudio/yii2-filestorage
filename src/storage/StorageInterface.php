<?php
/**
 * @copyright Copyright (c) 2025, Oleg Chulakov Studio
 * @link https://chulakov.ru
 */

declare(strict_types=1);

namespace chulakov\filestorage\storage;

use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\uploaders\UploadInterface;
use Intervention\Image\Image;

interface StorageInterface
{
    /**
     * Получение ссылки на файл модели
     */
    public function getFileUrl(BaseFile $model, bool $isAbsolute = true): string;

    /**
     * Получить путь сохраненного файла из модели
     */
    public function getUploadPath(BaseFile $model): ?string;

    /**
     * Сохранение файла
     */
    public function saveFile(UploadInterface $file, UploadParams $params): ?string;

    /**
     * Добавление в URL адрес исходной точки
     */
    public function convertToUrl(string $path, bool $isAbsolute = false): string;

    /**
     * Удаление файла
     */
    public function removeFile(BaseFile|string $file): void;

    /**
     * Формирование абсолютного пути до файлов с созданием новой директории, если ее еще не существует
     */
    public function makePath(string $path, PathParams $params): string;

    /**
     * Поиск файлов, подходящих для удаления
     *
     * @return string[]
     */
    public function searchAllFiles(string $path, PathParams $params): array;

    /**
     * Возвращает полный путь к файлу в файловой системе
     */
    public function getFilePath(BaseFile $model): string;

    /**
     * Формирование абсолютного пути до файлов с созданием новой директории, если ее еще не существует
     */
    public function getAbsolutePath(string $path): string;

    /**
     * Формирование относительного пути с учетом настроек и переданных параметров
     */
    public function getSavePath(UploadParams $params): string;

    /**
     * Удаление группы
     */
    public function removeGroup(string $group): void;

    /**
     * Удаление всех дополнительных файлов
     */
    public function removeAllFiles(BaseFile $file, PathParams $params): bool;

    /**
     * Сохранение изображения Intervention\Image
     */
    public function saveImage(Image $image, string $path, ?int $quality = null): bool;

    /**
     * Проверка существования файла
     */
    public function existFile(string $path): bool;
}
