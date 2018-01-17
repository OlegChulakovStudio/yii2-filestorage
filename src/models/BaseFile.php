<?php
/**
 * Файл класса BaseFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use yii\rbac\Item;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use chulakov\filestorage\behaviors\StorageBehavior;

/**
 * Class File
 *
 * @property $mime
 * @property $ori_extension
 * @property $ori_name
 * @property $sys_file
 * @property $size
 * @property $group_code
 * @property $object_id
 *
 * @method string getUrl(bool $isAbsolute, Item $role = null) Возвращает абсолютный или относительный URL-адрес к файлу
 * @method string getPath(Item $role = null)                  Возвращает полный путь к файлу в файловой системе
 * @method string getUploadUrl(bool $isAbsolute)       Возвращает URL-адрес до директории нахождения файлов определенного типа
 * @method string getUploadPath()                      Возвращает абсолютный путь к директории хранения файлов определенного типа
 */
abstract class BaseFile extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            StorageBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_code', 'ori_name', 'ori_extension', 'sys_file', 'mime'], 'required'],
            [['created_at', 'updated_at', 'size'], 'integer'],
            [['group_code', 'ori_extension'], 'string', 'max' => 16],
            [['object_id'], 'string', 'max' => 11],
            [['ori_name', 'sys_file', 'mime'], 'string', 'max' => 255],
            [['sys_file'], 'unique'],
        ];
    }

    /**
     * Установка системного пути до сохраненого файла
     *
     * @param string $name
     * @param string|null $path
     */
    public function setSystemFile($name, $path = null)
    {
        $this->sys_file = implode(DIRECTORY_SEPARATOR, array_filter([$path, $name]));
    }

    /**
     * Получение информации об оригинальном именовании файла
     *
     * @return string
     */
    public function getBaseName()
    {
        $pathInfo = pathinfo('_' . basename($this->sys_file), PATHINFO_FILENAME);
        return mb_substr($pathInfo, 1, mb_strlen($pathInfo, '8bit'), '8bit');
    }

    /**
     * Расширение сохраненного файла
     *
     * @return string
     */
    public function getExtension()
    {
        return strtolower(pathinfo($this->sys_file, PATHINFO_EXTENSION));
    }

    /**
     * Проверка файла на изображение
     *
     * @return bool
     */
    public function isImage()
    {
        return strpos($this->mime, 'image') === 0;
    }
}
