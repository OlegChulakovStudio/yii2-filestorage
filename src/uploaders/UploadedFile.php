<?php
/**
 * Файл класса UploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use chulakov\filestorage\observer\Event;
use chulakov\filestorage\observer\ObserverTrait;
use chulakov\filestorage\observer\ObserverInterface;
use Exception;

/**
 * Class UploadedFile
 * @package chulakov\filestorage\uploaders
 */
class UploadedFile extends \yii\web\UploadedFile implements UploadInterface, ObserverInterface
{
    /**
     * Подключение реализации функционала Observer
     */
    use ObserverTrait;

    /**
     * Системное имя файла
     *
     * @var string
     */
    protected $sysName;

    /**
     * Конфигурация компонента
     *
     * @param array $config
     * @return mixed|void
     * @throws \yii\base\InvalidConfigException
     */
    public function configure($config)
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
        $this->initListener();
    }

    /**
     * @inheritdoc
     */
    public function saveAs($file, $deleteFile = true)
    {
        if ($this->beforeSave($file, $deleteFile)) {
            parent::saveAs($file, false);
        }
        if ($deleteFile) {
            unlink($this->getFile());
        }
    }

    /**
     * Псевдособытие сохранения
     *
     * @param $savedPath
     * @param bool $deleteFile
     * @return bool
     */
    protected function beforeSave($savedPath, $deleteFile = true)
    {
        $event = $this->createEvent($savedPath, true, $deleteFile);
        $this->trigger(Event::SAVE_EVENT, $event);
        return $event->needSave;
    }

    /**
     * Удаление файла
     *
     * @param string $filePath
     * @param Exception $exception
     * @return bool
     */
    public function deleteFile($filePath, Exception $exception = null)
    {
        if ($this->beforeDelete($filePath, $exception)) {
            if (file_exists($filePath)) {
                return unlink($filePath);
            }
        }
        return true;
    }

    /**
     * Событие удаления файлов
     *
     * @param string $filePath
     * @param Exception $exception
     * @return bool
     */
    protected function beforeDelete($filePath, $exception)
    {
        $event = $this->createEvent(
            $filePath, false, true, $exception
        );
        $this->trigger(Event::DELETE_EVENT, $event);
        return $event->needDelete;
    }

    /**
     * Получить ссылку на файл
     *
     * @return string
     */
    public function getFile()
    {
        return $this->tempName;
    }

    /**
     * @return string Контент файла
     */
    public function getContent()
    {
        return file_get_contents($this->tempName);
    }

    /**
     * Получить имя файла с расширением
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Установка полного имени файла
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Получение MIME типа файла
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Установить mime тип файла
     *
     * @param string $mime
     */
    public function setType($mime)
    {
        $this->type = $mime;
    }

    /**
     * Получение размера файла
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Установить размер файла
     *
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     *  Получить системное имя файла
     *
     * @return string
     */
    public function getSysName()
    {
        if (empty($this->sysName)) {
            $this->sysName = uniqid();
        }
        return $this->sysName . '.' . $this->getExtension();
    }

    /**
     * Установить системное имя
     *
     * @param string $sysName
     */
    public function setSysName($sysName)
    {
        $this->sysName = $sysName;
    }

    /**
     * Установить расширение файла
     *
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->setName($this->getBaseName() . '.' . $extension);
    }
}