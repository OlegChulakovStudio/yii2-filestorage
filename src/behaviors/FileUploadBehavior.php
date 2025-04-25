<?php
/**
 * Файл класса FileUploadBehavior
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\behaviors;

use chulakov\filestorage\exceptions\NoAccessException;
use chulakov\filestorage\exceptions\NotUploadFileException;
use chulakov\filestorage\FileStorage;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\observer\UploadEvent;
use chulakov\filestorage\params\UploadParams;
use chulakov\filestorage\uploaders\UploadInterface;
use Closure;
use Exception;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\di\Instance;
use yii\rbac\Item;

/**
 * Поведение автоматической инициализации файла и загрузки его согласно настроек
 *
 * @package chulakov\filestorage\behaviors
 */
class FileUploadBehavior extends Behavior
{
    /**
     * @var Model|ActiveRecord
     */
    public $owner;
    public string $attribute = 'file';
    public ?string $name = null;
    public string $group = 'default';
    public ?string $type = null;
    public bool $skipOnEmpty = false;
    public bool $setErrors = false;
    public string $uploadClass = 'chulakov\filestorage\params\UploadParams';
    public ?string $uploadPattern = null;
    /**
     * @var array|callable
     */
    public $uploadOptions;
    public string|UploadInterface $repository = 'chulakov\filestorage\uploaders\UploadedFile';
    public array $repositoryOptions = [];
    public FileStorage|string|array $fileStorage = 'fileStorage';
    public Item|string|null $accessRole = null;
    /**
     * Класс модели, который необходимо создать при сохранении информации о файле
     */
    public ?string $modelClass = null;
    protected bool $isUploaded = false;
    protected bool $isMultiple = false;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (empty($this->attribute)) {
            throw new InvalidConfigException('Необходимо заполнить поле attribute!');
        }
        if (empty($this->name)) {
            $this->name = $this->attribute;
        }
        $this->fileStorage = Instance::ensure($this->fileStorage);
    }

    /**
     * @inheritdoc
     */
    public function events(): array
    {
        return [
            Model::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            UploadEvent::UPLOAD_EVENT => 'onUpload',
        ];
    }

    /**
     * Загрузка файлов через вызов события загрузки
     * @return BaseFile|BaseFile[]|null
     */
    public function upload(): BaseFile|array|null
    {
        $event = new UploadEvent();
        $this->owner->trigger(UploadEvent::UPLOAD_EVENT, $event);
        if (empty($event->uploadedFiles) === false) {
            $this->owner->trigger(UploadEvent::AFTER_UPLOAD_EVENT, $event);
            if ($this->isMultiple) {
                return $event->uploadedFiles;
            }
            return array_shift($event->uploadedFiles);
        }
        return null;
    }

    /**
     * Проверка и инициализация данных перед валидацией модели
     */
    public function beforeValidate()
    {
        if ($this->isInstances($this->owner->{$this->attribute})) {
            return;
        }
        $files = $this->getInstances();
        if (!empty($files)) {
            $this->configureInstances($files);
            if ($this->isInstances($files)) {
                $this->owner->{$this->attribute} = $files;
            }
        }
    }

    /**
     * Загрузка и сохранение файлов
     *
     * @param UploadEvent $event
     * @throws NoAccessException
     * @throws NotUploadFileException
     * @throws InvalidConfigException
     */
    public function onUpload($event)
    {
        if ($this->isUploaded) {
            return;
        }
        /** @var UploadInterface $files */
        $files = $this->owner->{$this->attribute};
        if (!$this->isInstances($files)) {
            if (empty($files) && !$this->skipOnEmpty) {
                throw new NotUploadFileException('Нет файлов для сохранения.');
            }
            return;
        }
        try {
            /** @var UploadParams $params */
            $params = Yii::$container->get(
                $this->uploadClass,
                [$this->group],
                $this->getUploadProperties(),
            );
        } catch (Exception $e) {
            throw new NotUploadFileException('Не удалось инициализировать DTO.', 0, $e);
        }
        try {
            $event->addUploadedFile($this->fileStorage->uploadFile($files, $params));
        } catch (NotUploadFileException $e) {
            if ($this->setErrors) {
                $this->owner->addError($this->attribute, $e->getMessage());
            } else {
                throw $e;
            }
        }
        $this->isUploaded = true;
    }

    /**
     * Формирование параметров для загрузки файла
     *
     * @return array
     */
    protected function getUploadProperties(): array
    {
        $properties = [
            'object_type' => $this->type,
            'accessRole' => $this->accessRole,
            'modelClass' => $this->modelClass,
            'pathPattern' => $this->uploadPattern,
        ];
        // Идентификатор связки с моделью
        if ($this->owner->hasMethod('getPrimaryKey')) {
            $primaryKeys = $this->owner->getPrimaryKey();
            if (is_array($primaryKeys)) {
                $primaryKeys = implode('-', $primaryKeys);
            }
            $properties['object_id'] = $primaryKeys;
        }
        // Расширенные параметры для формирования пути сохранения
        if ($extraProperties = $this->uploadOptions) {
            if ($extraProperties instanceof Closure) {
                $extraProperties = call_user_func($extraProperties, $properties);
            }
            $properties['options'] = (array) $extraProperties;
        }
        return $properties;
    }

    /**
     * Проверка, объявлены ли уже данные в модели
     *
     * @param mixed $model
     * @return bool
     */
    protected function isInstances($model)
    {
        return $model instanceof UploadInterface;
    }

    /**
     * Инициализация данных для модели
     * @return UploadInterface
     */
    protected function getInstances()
    {
        $repository = $this->repository;
        return $repository::getInstance($this->owner, $this->name);
    }

    /**
     * Конфигурация загруженного файла
     *
     * @param UploadInterface $file
     */
    protected function configureInstances($file): void
    {
        if (empty($this->repositoryOptions) === false) {
            $file->configure($this->repositoryOptions);
        }
    }
}
