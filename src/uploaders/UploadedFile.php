<?php
/**
 * Файл класса UploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use chulakov\filestorage\observer\ObserverInterface;
use chulakov\filestorage\observer\ObserverTrait;
use Throwable;
use yii\base\InvalidConfigException;
use yii\web\UploadedFile as YiiUploadedFile;

/**
 * Class UploadedFile
 * @package chulakov\filestorage\uploaders
 */
class UploadedFile extends YiiUploadedFile implements UploadInterface, ObserverInterface
{
    /**
     * Подключение реализации функционала Observer
     */
    use ObserverTrait;

    /**
     * Системное имя файла
     */
    protected string $sysName;

    /**
     * Конфигурация компонента
     *
     * @throws InvalidConfigException
     */
    public function configure(array $config): void
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
        $this->initListener();
    }

    /**
     * @inheritdoc
     */
    public function saveAs($file, $deleteTempFile = true): bool
    {
        $result = true;
        if ($this->beforeSave($file, $deleteTempFile)) {
            $result = parent::saveAs($file, false);
        }
        if ($deleteTempFile) {
            unlink($this->getFile());
        }
        return $result;
    }

    /**
     * Удаление файла
     */
    public function deleteFile(string $filePath, ?Throwable $exception = null): bool
    {
        if ($this->beforeDelete($filePath, $exception)) {
            if (file_exists($filePath)) {
                return unlink($filePath);
            }
        }
        return true;
    }

    /**
     * Получить ссылку на файл
     */
    public function getFile(): string
    {
        return $this->tempName;
    }

    /**
     * Получить контент файла
     */
    public function getContent(): string
    {
        return file_get_contents($this->tempName);
    }

    /**
     * Получить имя файла с расширением
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Установка полного имени файла
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Получение MIME типа файла
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Установить mime тип файла
     */
    public function setType(string $mime): void
    {
        $this->type = $mime;
    }

    /**
     * Получение размера файла
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Установить размер файла
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * Получить системное имя файла
     */
    public function getSysName(): string
    {
        if (empty($this->sysName)) {
            $this->sysName = uniqid();
        }
        return $this->sysName . '.' . $this->getExtension();
    }

    /**
     * Установить системное имя
     */
    public function setSysName(string $sysName): void
    {
        $this->sysName = $sysName;
    }

    /**
     * Установить расширение файла
     */
    public function setExtension(string $extension): void
    {
        $this->setName($this->getBaseName() . '.' . $extension);
    }

    /**
     * Необходимость удаление временного файла после загрузки
     */
    public function needDeleteTempFile(): bool
    {
        return true;
    }
}
