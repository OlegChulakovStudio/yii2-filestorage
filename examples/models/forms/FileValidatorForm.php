<?php
/**
 * Файл класса FileValidatorForm
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use chulakov\filestorage\behaviors\FileUploadBehavior;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\models\File;
use chulakov\filestorage\uploaders\UploadedFile;
use chulakov\filestorage\uploaders\UploadInterface;
use chulakov\filestorage\validators\FileValidator;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Класс для загрузки формы
 *
 * @package backend\models\forms
 *
 * @method File[] upload()
 */
class FileValidatorForm extends Model
{
    /**
     * Загружаемый файл
     *
     * @var UploadInterface
     */
    public $attachFile;
    /**
     * Загруженный ранее файл
     *
     * @var BaseFile
     */
    public $attachedFile;
    /**
     * Модель к которой файл привязан
     *
     * @var ActiveRecord
     */
    protected $model;

    /**
     * @param ActiveRecord $model
     * @param array $config
     */
    public function __construct($model = null, array $config = [])
    {
        // Прокидываем модель(если она есть) к которому привязываем файл.
        $this->setModel($model);

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        if ($this->model) {
            $this->setAttributes($this->model->getAttributes([
               // Подгружаем поля модели
            ]));
            // $this->model->image - relation связь с моделью Image.
            if ($image = $this->model->image) {
                $this->attachedFile = $image;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            // Проверяем, был ли загружен файл ранее или он пришел в форму.
            ['attachFile', FileValidator::class, 'targetAttribute' => 'attachedFile'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => FileUploadBehavior::class,
                'type' => 'animal',
                'group' => 'image',
                'attribute' => 'attachFile',
                'fileStorage' => 'fileStorage',
                'repository' => UploadedFile::class,
            ],
        ];
    }

    /**
     * Установить модель
     */
    protected function setModel(ActiveRecord $model): void
    {
        $this->model = $model;
    }

    /**
     * Пробрасывание ID
     */
    public function getPrimaryKey(): int
    {
        return !empty($this->model)
            ? $this->model->id
            : 0;
    }
}
