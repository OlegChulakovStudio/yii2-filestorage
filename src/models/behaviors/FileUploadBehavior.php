<?php
/**
 * Файл класса FileUploadBehavior.php
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class FileUploadBehavior extends Behavior
{
    public $attribute;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate()
    {
        if ($this->owner->{$this->attribute} instanceof UploadedFile) {
            return;
        }

        $file = UploadedFile::getInstance($this->owner, $this->attribute);

        if (empty($file)) {
            $file = UploadedFile::getInstanceByName($this->attribute);
        }
        if ($file instanceof UploadedFile) {
            $this->owner->{$this->attribute} = $file;
        }
    }
}