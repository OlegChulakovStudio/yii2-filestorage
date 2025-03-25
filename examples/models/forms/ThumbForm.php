<?php
/**
 * Файл класса ThumbForm
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use chulakov\filestorage\behaviors\FileUploadBehavior;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\managers\ThumbsManager;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\uploaders\UploadedFile;
use yii\base\Model;

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
                            'class' => ThumbsManager::class,
                            /** Расширение файла */
                            'encode' => 'jpg',
                            /** Качество */
                            'quality' => 80,
                            /** Ширина */
                            'width' => 192,
                            /** Высота */
                            'height' => 192,
                            /** Путь к водяной метке */
                            'watermarkPath' => '/path/to/file/watermark.png',
                            /** Позиция водяной метки */
                            'watermarkPosition' => Position::CENTER,
                            /** Компонент для работы с изображениями */
                            'imageComponent' => 'imageComponent',
                        ],
                    ],
                ],
            ],
        ];
    }
}
