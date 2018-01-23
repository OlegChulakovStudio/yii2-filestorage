<?php
/**
 * Файл класса BasicFileForm
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\models;

use yii\base\Model;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\uploaders\UploadedFile;
use chulakov\filestorage\behaviors\FileUploadBehavior;

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
    public function rules()
    {
        return [
            [['file'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                // Поведение загрузки. Два типа:
                // FileUploadBehavior - для загрузки одного файла
                // FileUploadBehavior - для загрузки множества файлов
                'class' => FileUploadBehavior::className(),
                'attribute' => 'file', // Аттирут формы, куда должен загрузиться файл
                'group' => 'files', // Группа файлов, куда будет сохранен файл
                'fileStorage' => 'fileStorage', // Файловое хранилище
                // Репозитории. Два типа:
                // UploadedFile - загрузка через POST запрос
                // RemoteUploadedFile - загрузка по ссылке
                'repository' => UploadedFile::class,
            ],
        ];
    }
}