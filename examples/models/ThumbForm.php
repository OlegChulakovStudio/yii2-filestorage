<?php
/**
 * Файл класса ThumbForm
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\models;

use yii\base\Model;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\managers\ThumbsManager;
use chulakov\filestorage\uploaders\UploadedFile;
use chulakov\filestorage\behaviors\FileUploadBehavior;

/**
 * Class ThumbForm
 * @package backend\models
 *
 * @method BaseFile[] upload()
 */
class ThumbForm extends Model
{
    /**
     * Загружаемый файл
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
                                'class' => ThumbsManager::className(), // Класс менеджера
                                'encode' => 'jpg', // Расширение файла
                                'quality' => 80, // Качество
                                'width' => 192, // Ширина
                                'height' => 192, // Высота
                                'watermarkPath' => '/path/to/file/watermark.png', // Путь к водяной метке
                                'watermarkPosition' => Position::CENTER, // Позиция водяной метки
                                'imageClass' => ImageComponent::className(), // Компонент для работы с изображениями
                            ],
                        ],
                ]
            ],
        ];
    }
}