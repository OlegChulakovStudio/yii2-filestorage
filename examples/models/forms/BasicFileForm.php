<?php
/**
 * Файл класса BasicFileForm
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use chulakov\filestorage\behaviors\FileUploadBehavior;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\uploaders\UploadedFile;
use yii\base\Model;

/**
 * Class BasicFileForm
 * @package backend\models
 *
 * @method BaseFile[] upload()
 */
class BasicFileForm extends Model
{
    /**
     * Загружаемый файл
     *
     * @var array
     */
    public $file;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['file'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            [
                /**
                 * Поведение загрузки. Два типа:
                 * FileUploadBehavior - для загрузки одного файла
                 * FileUploadBehavior - для загрузки множества файлов
                 */
                'class' => FileUploadBehavior::class,
                /** Атрибут формы, куда должен загрузиться файл */
                'attribute' => 'file',
                /** Группа файлов, куда будет сохранен файл */
                'group' => 'files',
                /** Файловое хранилище */
                'fileStorage' => 'fileStorage',
                /** Репозитории. Два типа:
                 * UploadedFile - загрузка через POST запрос
                 * RemoteUploadedFile - загрузка по ссылке
                 */
                'repository' => UploadedFile::class,
            ],
        ];
    }
}
