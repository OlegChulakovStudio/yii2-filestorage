<?php
/**
 * Файл класса FileFormTest
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\tests;

use yii\base\Model;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\managers\ImageManager;
use chulakov\filestorage\uploaders\UploadedFile;
use chulakov\filestorage\managers\ThumbsManager;
use chulakov\filestorage\behaviors\FileUploadBehavior;

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
        $watermarkPath = \Yii::getAlias('@tests/data') . '/images/watermark/watermark.png';

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
                                'class' => ThumbsManager::className(),
                                'encode' => 'jpg',
                                'quality' => 80,
                                'watermarkPath' => $watermarkPath,
                                'watermarkPosition' => Position::CENTER,
                                'imageComponent' => 'imageComponent',
                            ],
                            [
                                'class' => ImageManager::className(),
                                'width' => 640,
                                'height' => 480,
                                'encode' => 'jpg',
                                'quality' => 100,
                                'watermarkPath' => $watermarkPath,
                                'watermarkPosition' => Position::CENTER,
                                'imageComponent' => 'imageComponent',
                            ]
                        ],
                ]
            ],
        ];
    }
}