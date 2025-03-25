<?php
/**
 * Файл класса FileFormTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use chulakov\filestorage\behaviors\FileUploadBehavior;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\managers\ImageManager;
use chulakov\filestorage\managers\ThumbsManager;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\uploaders\UploadedFile;
use chulakov\filestorage\uploaders\UploadInterface;
use Yii;
use yii\base\Model;

/**
 * Class FileForm
 * @package backend\models
 *
 * @method BaseFile[] upload()
 */
class FileFormTest extends Model
{
    /**
     * Загружаемый файл
     */
    public ?UploadInterface $image = null;

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
        $watermarkPath = Yii::getAlias('@tests/data') . '/images/watermark/watermark.png';

        return [
            [
                'class' => FileUploadBehavior::class,
                'attribute' => 'image',
                'group' => 'photos',
                'type' => 'animal',
                'fileStorage' => 'fileStorage',
                'repository' => UploadedFile::class,
                'repositoryOptions' => [
                    'listeners' =>
                        [
                            [
                                'class' => ThumbsManager::class,
                                'encode' => 'jpg',
                                'quality' => 80,
                                'watermarkPath' => $watermarkPath,
                                'watermarkPosition' => Position::CENTER,
                                'imageComponent' => 'imageComponent',
                            ],
                            [
                                'class' => ImageManager::class,
                                'width' => 640,
                                'height' => 480,
                                'encode' => 'jpg',
                                'quality' => 100,
                                'watermarkPath' => $watermarkPath,
                                'watermarkPosition' => Position::CENTER,
                                'imageComponent' => 'imageComponent',
                            ],
                        ],
                ],
            ],
        ];
    }
}
