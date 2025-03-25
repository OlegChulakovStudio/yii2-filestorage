<?php
/**
 * Файл класса StorageBehavior
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotFoundFileException;
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\models\BaseFile;
use Exception;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\di\Instance;
use yii\rbac\Item;

/**
 * Class StorageBehavior
 * @package chulakov\filestorage\behaviors
 */
class StorageBehavior extends Behavior
{
    /**
     * @var BaseFile
     */
    public $owner;
    /**
     * Класс компонента хранилища
     *
     * @var FileStorage
     */
    public $fileStorage = 'fileStorage';

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->fileStorage = Instance::ensure($this->fileStorage);
    }

    /**
     * @inheritdoc
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_DELETE => [$this, 'deleteFile'],
        ];
    }

    /**
     * Возвращает абсолютный или относительный URL-адрес к файлу
     *
     * @throws NoAccessException
     */
    public function getUrl(bool $isAbsolute = false, Item|string|null $role = null): bool|string
    {
        try {
            return $this->fileStorage->getFileUrl($this->owner, $isAbsolute, $role);
        } catch (NotFoundFileException $e) {
            return $this->exceptionResult($e);
        }
    }

    /**
     * Возвращает полный путь к файлу в файловой системе
     *
     * @throws NoAccessException
     */
    public function getPath(string|Item|null $role = null): string
    {
        return $this->fileStorage->getFilePath($this->owner, $role);
    }

    /**
     * Возвращает URL-адрес до директории нахождения файлов определенного типа
     */
    public function getUploadUrl(bool $isAbsolute = false): string
    {
        return $this->fileStorage->getUploadUrl($this->owner, $isAbsolute);
    }

    /**
     * Возвращает абсолютный путь к директории хранения файлов определенного типа
     *
     * @throws NoAccessException
     * @throws NotFoundFileException
     * @throws InvalidConfigException
     */
    public function getUploadPath(): string
    {
        return $this->fileStorage->getUploadPath($this->owner);
    }

    /**
     * Удаление файла
     */
    public function deleteFile(): void
    {
        $this->fileStorage->removeFile($this->owner);
    }

    /**
     * Возвращаемое значение при отсутствии файла
     */
    protected function exceptionResult(Exception $e): bool|string
    {
        Yii::error($e);
        return $this->owner->isImage()
            ? $this->fileStorage->getNoImage()
            : '';
    }
}
