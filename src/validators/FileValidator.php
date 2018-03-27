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
        $value = $model->{$attribute};
        if (($this->strict && $value instanceof UploadInterface) || !empty($value)) {
            return;
        }
        $targetValue = $model->{$this->targetAttribute};
        if (($this->strict && $targetValue instanceof BaseFile) || !empty($targetValue)) {
            return;
        }
        $this->addError($model, $attribute, $this->message);
    }
}