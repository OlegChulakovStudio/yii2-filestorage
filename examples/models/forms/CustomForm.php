<?php
/**
 * Файл класса CustomForm
 *
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

use backend\managers\MailManager;
use chulakov\filestorage\behaviors\FileUploadBehavior;
use chulakov\filestorage\models\BaseFile;
use chulakov\filestorage\uploaders\UploadedFile;
use yii\base\Model;

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
    public function rules(): array
    {
        return [
            [['file'], 'required'],
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
                'attribute' => 'file',
                'group' => 'files',
                'fileStorage' => 'fileStorage',
                'repository' => UploadedFile::class,
                'repositoryOptions' => [
                    'listeners' => [
                        [
                            'class' => MailManager::class,
                        ],
                    ],
                ],
            ],
        ];
    }
}
