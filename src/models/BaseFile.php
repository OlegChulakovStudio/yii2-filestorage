<?php
/**
 * Файл класса BaseFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models;

use chulakov\filestorage\behaviors\StorageBehavior;
use chulakov\filestorage\models\scopes\BaseFileQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\rbac\Item;

/**
 * Базовая модель информации о загруженном файле
 *
 * @property integer $id
 * @property string $group_code
 * @property int $object_id
 * @property string $object_type
 * @property string $ori_name
 * @property string $ori_extension
 * @property string $sys_file
 * @property string $mime
 * @property integer $size
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @method string getUrl(bool $isAbsolute = false, Item $role = null) Возвращает абсолютный или относительный URL-адрес к файлу
 * @method string getPath(Item $role = null) Возвращает полный путь к файлу в файловой системе
 * @method string getUploadUrl(bool $isAbsolute) Возвращает URL-адрес до директории нахождения файлов определенного типа
 * @method string getUploadPath() Возвращает абсолютный путь к директории хранения файлов определенного типа
 */
abstract class BaseFile extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%file}}';
    }

    /**
     * Инициализация корректной модели файла
     *
     * @param array $row
     */
    public static function instantiate($row): static
    {
        if (static::checkIsImage($row['mime'])) {
            return new Image();
        }
        return new File();
    }

    /**
     * Проверка mime типа на изображение
     */
    public static function checkIsImage(string $mime): bool
    {
        return str_starts_with($mime, 'image');
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
            StorageBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['group_code', 'ori_name', 'ori_extension', 'sys_file', 'mime'], 'required'],
            [['created_at', 'updated_at', 'size'], 'integer'],
            [['group_code', 'ori_extension'], 'string', 'max' => 16],
            [['object_id'], 'integer'],
            [['object_type'], 'string', 'max' => 16],
            [['ori_name', 'sys_file', 'mime'], 'string', 'max' => 255],
            [['sys_file'], 'unique'],
        ];
    }

    /**
     * Установка системного пути до сохраненного файла
     */
    public function setSystemFile(string $name, ?string $path = null): void
    {
        $this->sys_file = implode(DIRECTORY_SEPARATOR, array_filter([$path, $name]));
    }

    /**
     * Получение информации об оригинальном именовании файла
     */
    public function getBaseName(): string
    {
        $pathInfo = pathinfo('_' . basename($this->sys_file), PATHINFO_FILENAME);
        return mb_substr($pathInfo, 1, mb_strlen($pathInfo, '8bit'), '8bit');
    }

    /**
     * Расширение сохраненного файла
     */
    public function getExtension(): string
    {
        return strtolower(pathinfo($this->sys_file, PATHINFO_EXTENSION));
    }

    /**
     * Проверка файла на изображение
     */
    public function isImage(): bool
    {
        return static::checkIsImage($this->mime);
    }

    /**
     * Модель поиска файла
     */
    public static function find(): BaseFileQuery
    {
        return new BaseFileQuery(get_called_class());
    }
}
