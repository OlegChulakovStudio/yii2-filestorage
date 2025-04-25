<?php
/**
 * Файл класса RemoteFileForm
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use chulakov\filestorage\behaviors\FileUploadBehavior;
use chulakov\filestorage\image\Position;
use chulakov\filestorage\managers\ImageManager;
use chulakov\filestorage\managers\ThumbsManager;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\uploaders\RemoteUploadedFile;
use yii\base\Model;

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
    public function rules(): array
    {
        return [
            [['link'], 'required'],
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
                'attribute' => 'link',
                'group' => 'files',
                'fileStorage' => 'fileStorage',
                'repository' => RemoteUploadedFile::class,
                'repositoryOptions' => [
                    'listeners' => [
                        [
                            'class' => ThumbsManager::class,
                            'encode' => 'jpg',
                            'quality' => 80,
                            'watermarkPath' => '/path/to/watermark/watermark.png',
                            'watermarkPosition' => Position::CENTER,
                            'imageComponent' => 'imageComponent',
                        ],
                        [
                            'class' => ImageManager::class,
                            'width' => 640,
                            'height' => 480,
                            'encode' => 'jpg',
                            'quality' => 100,
                            'watermarkPath' => '/path/to/watermark/watermark.png',
                            'watermarkPosition' => Position::CENTER,
                            'imageComponent' => 'imageComponent',
                            'accessRole' => 'role_example',
                        ],
                    ],
                ],
            ],
        ];
    }
}
