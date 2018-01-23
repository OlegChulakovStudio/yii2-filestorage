<?php
/**
 * Файл класса CustomForm
 *
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace backend\models;

use yii\base\Model;
use backend\managers\MailManager;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\uploaders\UploadedFile;
use chulakov\filestorage\behaviors\FileUploadBehavior;

/**
 * Class CustomForm
 * @package backend\models
 *
 * @method BaseFile[] upload()
 */
class CustomForm extends Model
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
                'class' => FileUploadBehavior::className(),
                'attribute' => 'file',
                'group' => 'files',
                'fileStorage' => 'fileStorage',
                'repository' => UploadedFile::class,
                'repositoryOptions' => [
                    'listeners' =>
                        [
                            [
                                'class' => MailManager::class,
                            ]
                        ],
                ]
            ],
        ];
    }
}