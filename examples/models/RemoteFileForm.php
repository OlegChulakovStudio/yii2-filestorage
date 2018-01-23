<?php
/**
 * Файл класса RemoteFileForm
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\models;

use yii\base\Model;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\ImageComponent;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\managers\ImageManager;
use chulakov\filestorage\managers\ThumbsManager;
use chulakov\filestorage\uploaders\RemoteUploadedFile;
use chulakov\filestorage\behaviors\FileUploadBehavior;

/**
 * Class RemoteFileForm
 * @package backend\models
 *
 * @method BaseFile[] upload()
 */
class RemoteFileForm extends Model
{
    /**
     * Ссылка на файл
     *
     * @var array
     */
    public $link;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['link'], 'required']
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
                'attribute' => 'link',
                'group' => 'files',
                'fileStorage' => 'fileStorage',
                'repository' => RemoteUploadedFile::class,
                'repositoryOptions' => [
                    'listeners' =>
                        [
                            [
                                'class' => ThumbsManager::className(),
                                'encode' => 'jpg',
                                'quality' => 80,
                                'watermarkPath' => '/path/to/watermark/watermark.png',
                                'watermarkPosition' => Position::CENTER,
                                'imageClass' => ImageComponent::className(),
                            ],
                            [
                                'class' => ImageManager::className(),
                                'width' => 640,
                                'height' => 480,
                                'encode' => 'jpg',
                                'quality' => 100,
                                'watermarkPath' => '/path/to/watermark/watermark.png',
                                'watermarkPosition' => Position::CENTER,
                                'imageClass' => ImageComponent::className(),
                                'accessRole' => 'role_example',
                            ]
                        ],
                ]
            ],
        ];
    }
}