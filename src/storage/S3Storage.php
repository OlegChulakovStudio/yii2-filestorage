<?php
/**
 * @copyright Copyright (c) 2025, Oleg Chulakov Studio
 * @link https://chulakov.ru
 */

declare(strict_types=1);

namespace chulakov\filestorage\storage;

use AsyncAws\S3\S3Client;
use chulakov\filestorage\exceptions\NotFoundFileException;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\observer\ObserverInterface;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\services\PathService;
use chulakov\filestorage\uploaders\UploadInterface;
use Intervention\Image\Image;
use League\Flysystem\AsyncAwsS3\AsyncAwsS3Adapter;
use League\Flysystem\AsyncAwsS3\PortableVisibilityConverter;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Throwable;
use Yii;

final class S3Storage extends BaseStorage
{
    private FilesystemOperator $fileSystem;

    public function __construct(
        string $endpoint,
        string $accessKeyId,
        string $accessKeySecret,
        string $bucket,
        string $pathPrefix = '',
        ?string $region = null,
    ) {
        $config = array_filter(compact('region', 'endpoint', 'accessKeyId', 'accessKeySecret'));
        $client = new S3Client($config);

        $adapter = new AsyncAwsS3Adapter(
            $client,
            $bucket,
            $pathPrefix,
            new PortableVisibilityConverter(),
        );

        $this->fileSystem = new Filesystem(
            $adapter,
            [
                'public_url' => implode('/', [
                    rtrim($endpoint, '/'),
                    $bucket,
                    ltrim($pathPrefix, '/'),
                ]),
            ],
        );

        $this->pathService = new PathService(
            $endpoint,
            $bucket,
            $endpoint,
        );
    }

    /**
     * Формирование абсолютного пути до файлов
     */
    public function getAbsolutePath(string $path): string
    {
        //NOTE в S3 все пути к файлам относительные
        return $path;
    }

    /**
     * @inheritDoc
     * @throws FilesystemException|NotFoundFileException
     */
    public function getFileUrl(BaseFile $model, bool $isAbsolute = true): string
    {
        $file = $model->sys_file;
        return $this->existFile($file)
            ? $this->fileSystem->publicUrl($file)
            : throw new NotFoundFileException('Не удалось найти файл :' . $file);
    }

    /**
     * @throws FilesystemException
     */
    public function existFile(string $path): bool
    {
        return $this->fileSystem->fileExists($path);
    }

    /**
     * @inheritDoc
     */
    public function getUploadPath(BaseFile $model): ?string
    {
        return $model->sys_file;
    }

    /**
     * @inheritDoc
     * @throws FilesystemException
     */
    public function saveFile(UploadInterface $file, UploadParams $params): ?string
    {
        $savePath = $this->getSavePath($params);
        $filePath = implode(DIRECTORY_SEPARATOR, [$savePath, $file->getSysName()]);

        if ($file instanceof ObserverInterface) {
            $file->beforeSave($filePath, $file->needDeleteTempFile());
        }

        $this->writeFileContent($filePath, $file->getContent());

        if ($file->needDeleteTempFile()) {
            unlink($file->getFile());
        }

        return $savePath;
    }

    public function writeFileContent(string $path, string $content): bool
    {
        try {
            $this->fileSystem->write($path, $content);
            return true;
        } catch (FilesystemException $e) {
            Yii::error($e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function convertToUrl(string $path, bool $isAbsolute = false): string
    {
        return $this->fileSystem->publicUrl($path);
    }

    /**
     * @inheritDoc
     */
    public function removeFile(BaseFile|string $file): void
    {
        try {
            $this->fileSystem->delete(
                is_string($file) ? $file : $file->sys_file,
            );
        } catch (Throwable $e) {
            Yii::error($e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getFilePath(BaseFile $model): string
    {
        return $model->sys_file;
    }

    /**
     * @inheritDoc
     */
    public function removeGroup(string $group): void
    {
        try {
            $this->fileSystem->deleteDirectory($group);
        } catch (FilesystemException $e) {
            Yii::error($e);
        }
    }

    public function removeAllFiles(BaseFile $file, PathParams $params): bool
    {
        $files = $this->searchAllFiles($file->sys_file, $params);

        foreach ($files as $file) {
            try {
                $this->fileSystem->delete($file->path());
            } catch (FilesystemException $e) {
                Yii::error($e);
            }
        }

        return true;
    }

    /**
     * @return FileAttributes[]
     * @throws FilesystemException
     */
    public function searchAllFiles(string $path, PathParams $params): array
    {
        $config = $this->pathService->parseConfig($path, $params);
        $pattern = $this->pathService->parsePattern($params->searchPattern, $config);

        return $this->fileSystem
            ->listContents(dirname($path), true)
            ->filter(static fn (StorageAttributes $file) => $file->isFile() && fnmatch($pattern, $file->path()))
            ->toArray();
    }

    public function saveImage(Image $image, string $path, ?int $quality = null): bool
    {
        try {
            $this->writeFileContent($path, $image->stream(quality: $quality)->getContents());
            return true;
        } catch (FilesystemException $e) {
            Yii::error($e);
            return false;
        }
    }
}
