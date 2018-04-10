<?php
/**
 * Файл класса ImageForm
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use yii\base\Model;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\managers\ImageManager;
use chulakov\filestorage\uploaders\UploadedFile;
use chulakov\filestorage\behaviors\FileUploadBehavior;

/**
 * Class ImageForm
 * @package backend\models
 *
 * @method BaseFile[] upload()
 */
class ImageForm extends Model
{
    /**
     * Загружаемое изображение
     *
     * @var array
     */
    public $image;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => FileUploadBehavior::className(),
                'attribute' => 'image',
                'group' => 'photos',
                'fileStorage' => 'fileStorage',
                'repository' => UploadedFile::class,
                'repositoryOptions' => [
                    'listeners' =>
                        [
                            [
                                'class' => ImageManager::className(), // Класс менеджера
                                'width' => 640, // Ширина
                                'height' => 480, // Высота
                                'encode' => 'jpg', // Расширение файла
                                'quality' => 100, // Качество изображений
                                'watermarkPath' => '/path/to/watermark/watermark.png', // Путь к водяной метке
                                'watermarkPosition' => Position::CENTER, // Позиция водяной метки
                                'imageComponent' => 'imageComponent', // Компонент для работы с изображениями
                                'accessRole' => 'role_example', // Роль для RBAC
                            ]
                        ],
                ]
            ],
        ];
    }
}