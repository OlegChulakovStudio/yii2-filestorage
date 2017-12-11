<?php
/**
 * Файл класса FileForm.php
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models\forms;

use chulakov\filestorage\models\behaviors\FileUploadBehavior;
use yii\base\Model;

class FileForm extends Model
{
    /**
     * Загружаемый файл
     *
     * @var $file
     */
    public $file;

    /**
     * Разрешенные типы файлов
     *
     * @var array $extensions
     */
    public $extensions;

    public function __construct($extensions, $config = [])
    {
        $this->extensions = $extensions;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $extensions = implode(', ', $this->extensions);
        return [
            [
                ['file'], 'file', 'skipOnEmpty' => false,
                'extensions' => $extensions
            ]
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
            ],
        ];
    }
}