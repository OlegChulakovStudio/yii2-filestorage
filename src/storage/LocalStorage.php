<?php
/**
 * @copyright Copyright (c) 2025, Oleg Chulakov Studio
 * @link https://chulakov.ru
 */

declare(strict_types=1);

namespace chulakov\filestorage\storage;

use chulakov\filestorage\exceptions\NotFoundFileException;
use chulakov\filestorage\exceptions\NotUploadFileException;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\services\PathService;
use chulakov\filestorage\uploaders\UploadInterface;
use Intervention\Image\Image;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

final class LocalStorage extends BaseStorage
{
    public function __construct(
        $storagePath = '@webroot',
        $storageBaseUrl = false,
        $storageDir = 'upload',
        $fileMode = 0o755,
    ) {
        $this->pathService = new PathService(
            Yii::getAlias($storagePath),
            $storageDir,
            $storageBaseUrl,
            $fileMode,
        );
    }

    /**
     * @throws NotFoundFileException
     */
    public function getFileUrl(BaseFile $model, bool $isAbsolute = false): string
    {
        $path = $this->getFilePath($model);

        return $this->convertToUrl($path, $isAbsolute);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotUploadFileException
     */
    public function saveFile(UploadInterface $file, UploadParams $params): ?string
    {
        $path = $this->getSavePath($params);
        $full = $this->getAbsolutePath($path);
        if ($this->pathService->checkPath($full) === false) {
            throw new NotUploadFileException('Недостаточно прав для сохранения файла.');
        }
        $save = $file->saveAs($full . DIRECTORY_SEPARATOR . $file->getSysName());

        return $save ? $path : null;
    }

    /**
     * Удаление сохраненного файла
     */
    public function removeFile(BaseFile|string $file): void
    {
        try {
            $this->pathService->removeFile(
                is_string($file) ? $file : $file->getPath(),
            );
        } catch (NotFoundFileException $e) {
            Yii::error($e);
        }
    }

    /**
     * Получить путь сохраненного файла из модели
     */
    public function getUploadPath(BaseFile $model): ?string
    {
        return dirname($this->getFilePath($model));
    }

    public function removeGroup(string $group): void
    {
        try {
            FileHelper::removeDirectory(
                $this->pathService->getAbsolutePath($group),
            );
        } catch (ErrorException $e) {
            Yii::error($e);
        }
    }

    public function removeAllFiles(BaseFile $file, PathParams $params): bool
    {
        $origPath = $this->getAbsolutePath($file->sys_file);
        $files = $this->searchAllFiles($origPath, $params);
        foreach ($files as $file) {
            if (file_exists($file)) {
                $dirName = dirname($file);
                if (is_dir($dirName)) {
                    FileHelper::removeDirectory($dirName);
                }
            }
        }
        return true;
    }

    /**
     * @return string[]
     */
    public function searchAllFiles(string $path, PathParams $params): array
    {
        return $this->pathService->searchAllFiles($path, $params);
    }

    public function saveImage(Image $image, string $path, ?int $quality = null): bool
    {
        $dir = dirname($path);
        if (is_dir($dir) === false) {
            FileHelper::createDirectory($dir);
        }

        return (bool) $image->save($path, $quality);
    }

    public function existFile(string $path): bool
    {
        return is_file($path);
    }

    public function writeFileContent(string $path, string $content): bool
    {
        $dir = dirname($path);
        if (is_dir($dir) === false) {
            FileHelper::createDirectory($dir);
        }

        return (bool) file_put_contents($path, $content);
    }
}
