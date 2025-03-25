<?php
/**
 * Файл класса ImageForm
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use chulakov\filestorage\behaviors\FileUploadBehavior;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\managers\ImageManager;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\uploaders\UploadedFile;
use yii\base\Model;

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
    public function rules(): array
    {
        return [
            [['image'], 'required'],
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
                'attribute' => 'image',
                'group' => 'photos',
                'fileStorage' => 'fileStorage',
                'repository' => UploadedFile::class,
                'repositoryOptions' => [
                    'listeners' => [
                        [
                            /** Класс менеджера */
                            'class' => ImageManager::class,
                            /** Ширина */
                            'width' => 640,
                            /** Высота */
                            'height' => 480,
                            /** Расширение файла */
                            'encode' => 'jpg',
                            /** Качество изображений */
                            'quality' => 100,
                            /** Путь к водяной метке */
                            'watermarkPath' => '/path/to/watermark/watermark.png',
                            /** Позиция водяной метки */
                            'watermarkPosition' => Position::CENTER,
                            /** Компонент для работы с изображениями */
                            'imageComponent' => 'imageComponent',
                            /** Роль для RBAC */
                            'accessRole' => 'role_example',
                        ],
                    ],
                ],
            ],
        ];
    }
}
