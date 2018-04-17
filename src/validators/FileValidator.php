<?php
/**
 * Файл класса FileValidator
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\validators;

use yii\validators\Validator;
use yii\base\InvalidConfigException;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\uploaders\UploadInterface;

/**
 * Класс валидатора для проверки наличия файла у модели
 */
class FileValidator extends Validator
{
    /**
     * @var bool Обязательное соответствие типов
     */
    public $strict = false;
    /**
     * @var BaseFile Модель с файлом
     */
    public $targetAttribute;
    /**
     * @var bool
     */
    public $skipOnEmpty = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->targetAttribute)) {
            throw new InvalidConfigException("Некорректная настройка attachedFile.");
        }
        if ($this->message === null) {
            $this->message = \Yii::t('yii', '{attribute} is invalid.');
        }
        parent::init();
    }

    /**
     * Валидация
     *
     * @param \yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        if ($this->validateFile($model->{$attribute}, UploadInterface::class)) {
            return;
        }
        if ($this->validateFile($model->{$this->targetAttribute}, BaseFile::class)) {
            return;
        }
        $this->addError($model, $attribute, $this->message);
    }

    /**
     * Валидация атрибута на соответствие интерфейсу
     *
     * @param object|object[] $value
     * @param string $className
     * @return bool
     */
    protected function validateFile($value, $className)
    {
        if (empty($value)) {
            return false;
        }
        if ($this->strict) {
            if (!is_array($value)) {
                $value = [$value];
            }
            foreach ($value as $file) {
                if (!is_a($file, $className)) {
                    return false;
                }
            }
        }
        return true;
    }
}
