<?php
/**
 * @copyright Copyright (c) 2025, Oleg Chulakov Studio
 * @link https://chulakov.ru
 */

declare(strict_types=1);

namespace chulakov\filestorage\storage;

use chulakov\filestorage\exceptions\NotFoundFileException;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\services\PathService;
use yii\di\Instance;

abstract class BaseStorage implements StorageInterface
{
    /**
     * Паттерн генерации пути сохранения файлов
     * Допустимые токены:
     *  {id} - Подставляет в путь идентификатор модели, для которой загружается файл (object_id)
     *  {group} - Подставляет в путь группу файлов (group_code)
     */
    protected string $storagePattern = '{group}/{id}';
    /**
     * Объект для генерации пути сохранения файлов
     *
     * @var string|array
     */
    protected string|array $storagePropertyClass = 'chulakov\filestorage\params\PathParams';
    protected ?PathService $pathService = null;

    /**
     * Возвращает полный путь к файлу
     *
     * @throws NotFoundFileException
     */
    public function getFilePath(BaseFile $model): string
    {
        return $this->pathService?->findPath(
            $model->sys_file,
            $this->getPathFromModel($model),
        ) ?? '';
    }

    /**
     * Формирование пути до загружаемых файлов из данных модели
     */
    protected function getPathFromModel(BaseFile $model): string
    {
        $params = $this->getParamsFromModel($model);

        return $this->getSavePath($params);
    }

    /**
     * Получение параметров загрузки из модели
     */
    protected function getParamsFromModel(BaseFile $model): UploadParams
    {
        $params = new UploadParams($model->group_code);

        if ($model->object_id) {
            $params->object_id = $model->object_id;
        }

        return $params;
    }

    /**
     * Формирование относительного пути с учетом настроек и переданных параметров
     */
    public function getSavePath(UploadParams $params): string
    {
        /** @var PathParams $pathParams */
        $pathParams = Instance::ensure($this->storagePropertyClass, PathParams::class);
        $pathParams->group = $params->group_code;
        if ($params->pathPattern) {
            $pathParams->pathPattern = $params->pathPattern;
        } else {
            $pathParams->pathPattern = $this->storagePattern;
        }

        return $this->pathService?->savedPath($pathParams, $params->options()) ?? '';
    }

    /**
     * Добавление в URL адрес исходной точки
     */
    public function convertToUrl(string $path, bool $isAbsolute = false): string
    {
        return $this->pathService?->convertToUrl($path, $isAbsolute) ?? '';
    }

    /**
     * Формирование абсолютного пути до файлов с созданием новой директории, если ее еще не существует
     */
    public function makePath(string $path, PathParams $params): string
    {
        return $this->pathService?->makePath($path, $params) ?? '';
    }

    /**
     * Формирование абсолютного пути до файлов с созданием новой директории, если ее еще не существует
     */
    public function getAbsolutePath(string $path): string
    {
        return $this->pathService?->getAbsolutePath($path) ?? '';
    }
}
