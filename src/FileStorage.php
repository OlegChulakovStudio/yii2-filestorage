<?php
/**
 * Файл класса FileStorage
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

declare(strict_types=1);

namespace chulakov\filestorage;

use chulakov\filestorage\exceptions\DBModelException;
use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotUploadFileException;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\observer\ObserverInterface;
use chulakov\filestorage\observer\SaveModelEvent;
use chulakov\filestorage\params\PathParams;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\services\FileService;
use chulakov\filestorage\storage\StorageInterface;
use chulakov\filestorage\uploaders\UploadInterface;
use Exception;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\UnknownClassException;
use yii\rbac\Item;

/**
 * Class FileStorage
 * @package chulakov\filestorage
 */
class FileStorage extends Component
{
    /**
     * Плейсхолдер если файл изображения не найден
     */
    public string $imagePlaceholder = 'https://images.placeholders.dev/?width={width}&height={height}&text={text}';
    /**
     * Изображение заглушка, на тот случай если изображение не найдено
     */
    public string $imageNotFound = '';

    /**
     * Конструктор с зависимостью от сервиса
     */
    public function __construct(
        protected FileService $service,
        protected StorageInterface $storage,
        array $config = [],
    ) {
        parent::__construct($config);
    }

    /**
     * Загрузка файла
     *
     * @param UploadInterface|UploadInterface[] $files
     * @return BaseFile|BaseFile[]
     * @throws NoAccessException
     * @throws NotUploadFileException
     */
    public function uploadFile(array|UploadInterface $files, UploadParams $params): array|BaseFile
    {
        $this->canAccess($params->accessRole);

        /** @var BaseFile[] $result */
        $result = [];
        if ($single = is_array($files) === false) {
            $files = [$files];
        }
        try {
            /** @var UploadInterface[] $files */
            foreach ($files as $file) {
                $result[] = $this->saveFile($file, $params);
            }
        } catch (Exception $e) {
            $this->clearFiles($files, $params, $e);
            $this->clearModel($result);
            throw new NotUploadFileException('Не удалось сохранить файл', 0, $e);
        }
        return $single ? array_shift($result) : $result;
    }

    /**
     * Возвращает абсолютный путь к директории хранения файлов определенного типа
     */
    public function getUploadPath(BaseFile $model): string
    {
        return $this->storage->getUploadPath($model);
    }

    /**
     * Возвращает URL-адрес до директории нахождения файлов определенного типа
     */
    public function getUploadUrl(BaseFile $model, bool $isAbsolute = false): string
    {
        return $this->convertToUrl($this->getUploadPath($model), $isAbsolute);
    }

    /**
     * Возвращает полный путь к файлу в файловой системе
     *
     * @throws NoAccessException
     */
    public function getFilePath(BaseFile $model, Item|string|null $role = null): string
    {
        $this->canAccess($role, $model);
        return $this->storage->getFilePath($model);
    }

    /**
     * Возвращает абсолютный или относительный URL-адрес к файлу
     *
     * @throws NoAccessException
     */
    public function getFileUrl(BaseFile $model, bool $isAbsolute = false, Item|string|null $role = null): string
    {
        $this->canAccess($role, $model);

        return $this->storage->getFileUrl($model, $isAbsolute);
    }

    /**
     * Формирование абсолютного пути до файлов с созданием новой директории, если ее еще не существует
     */
    public function getAbsolutePath(string $path): string
    {
        return $this->storage->getAbsolutePath($path);
    }

    /**
     * Удаление сохраненного файла модели
     */
    public function removeFile(BaseFile $model): void
    {
        $this->storage->removeFile($model);
    }

    /**
     * Удаление группы файлов
     */
    public function removeGroup(string $group): void
    {
        $this->storage->removeGroup($group);
    }

    /**
     * Формирование полного пути до файла по шаблону
     */
    public function makePath($path, PathParams $params): string
    {
        return $this->storage->makePath($path, $params);
    }

    /**
     * Добавление в URL адрес исходной точки
     */
    public function convertToUrl(string $path, bool $isAbsolute = false): string
    {
        return $this->storage->convertToUrl($path, $isAbsolute);
    }

    /**
     * Выдача ошибочного изображения с информацией об отсутствии оригинального
     */
    public function getNoImage(int $w = 50, int $h = 50): bool|string
    {
        $path = $this->imageNotFound;
        if (empty($path) === false) {
            $path = Yii::getAlias($path);
            // Проверяем, является путь готовым URL
            if (preg_match('/^https?:/ui', $path)) {
                return $path;
            }
            // Если файл существует, то очищаем из него полный путь до web папки
            if (file_exists($path)) {
                $path = str_replace(Yii::getAlias('@webroot'), '', $path);
            }

            return $this->storage->convertToUrl($path);
        }

        return strtr($this->imagePlaceholder, [
            '{width}' => $w,
            '{height}' => $h,
            '{text}' => 'no-image',
        ]);
    }

    /**
     * Событие перед сохранением модели в базу данных
     */
    public function beforeModelSave(UploadInterface $file, BaseFile $model): void
    {
        $event = new SaveModelEvent(['model' => $model]);
        if ($file instanceof ObserverInterface) {
            $file->trigger(SaveModelEvent::BEFORE_MODEL_SAVE, $event);
        }
        $this->trigger(SaveModelEvent::BEFORE_MODEL_SAVE, $event);
    }

    /**
     * Событие после сохранения модели в базу данных
     */
    public function afterModelSave(UploadInterface $file, BaseFile $model): void
    {
        $event = new SaveModelEvent(['model' => $model]);
        if ($file instanceof ObserverInterface) {
            $file->trigger(SaveModelEvent::AFTER_MODEL_SAVE, $event);
        }
        $this->trigger(SaveModelEvent::AFTER_MODEL_SAVE, $event);
    }

    /**
     * Проверка прав доступа к файлу
     *
     * @throws NoAccessException
     */
    protected function canAccess(Item|string|null $role = null, ?BaseFile $model = null): bool
    {
        if (empty($role)) {
            return true;
        }
        $params = [];
        if ($model !== null) {
            $params['file'] = $model;
        }
        if (Yii::$app->user->can($role, $params)) {
            return true;
        }
        throw new NoAccessException('Нет прав доступа не сохранение файла.');
    }

    /**
     * Сохранение файла и создание для него модели с мета данными
     *
     * @throws DBModelException
     * @throws NotUploadFileException
     */
    protected function saveFile(UploadInterface $file, UploadParams $params): ?BaseFile
    {
        if ($path = $this->storage->saveFile($file, $params)) {
            if ($model = $this->createModel($file, $params)) {
                $model->setSystemFile($file->getSysName(), $path);
                $this->beforeModelSave($file, $model);
                if ($this->service->save($model)) {
                    $this->afterModelSave($file, $model);
                    return $model;
                }
            }
        }

        throw new NotUploadFileException('Не удалось сохранить данные о файле: ' . $file->getBaseName());
    }

    /**
     * Создание модели мета информации о файле
     */
    protected function createModel(UploadInterface $file, UploadParams $params): ?BaseFile
    {
        try {
            if ($params->modelClass !== null) {
                return $this->service->createUpload($params->modelClass, $file, $params);
            }
            return $this->service->createFile($file, $params);
        } catch (UnknownClassException $e) {
            Yii::error($e);
            return null;
        }
    }

    /**
     * Зачистка моделей
     *
     * @param BaseFile[] $models
     */
    protected function clearModel(array $models): void
    {
        foreach ($models as $model) {
            try {
                $model->delete();
            } catch (Exception | Throwable $e) {
                Yii::error($e);
            }
        }
    }

    /**
     * Зачистка файлов
     *
     * @param UploadInterface[] $files
     */
    protected function clearFiles(array $files, UploadParams $params, ?Throwable $e = null): void
    {
        $path = $this->getAbsolutePath($this->getSavePath($params));
        foreach ($files as $file) {
            try {
                $file->deleteFile($path . DIRECTORY_SEPARATOR . $file->getSysName(), $e);
            } catch (Exception $e) {
                Yii::error($e);
            }
        }
    }

    /**
     * Формирование относительного пути с учетом настроек и переданных параметров
     */
    protected function getSavePath(UploadParams $params): string
    {
        return $this->storage->getSavePath($params);
    }

    public function removeAllFiles(BaseFile $file, PathParams $params): bool
    {
        return $this->storage->removeAllFiles($file, $params);
    }

    public function existFile(string $filePath): bool
    {
        return $this->storage->existFile($filePath);
    }
}
