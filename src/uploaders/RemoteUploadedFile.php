<?php
/**
 * Файл класса RemoteUploadedFile
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\uploaders;

use chulakov\filestorage\exceptions\NotUploadFileException;
use chulakov\filestorage\observer\ObserverInterface;
use chulakov\filestorage\observer\ObserverTrait;
use Throwable;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

/**
 * Class RemoteUploadedFile
 * @package chulakov\filestorage\uploaders
 */
final class RemoteUploadedFile extends BaseObject implements UploadInterface, ObserverInterface
{
    /**
     * Подключение реализации функционала Observer
     */
    use ObserverTrait;

    /**
     * Ссылка на файл (UploadedFiles::tempName)
     */
    protected string $link;
    /**
     * Содержимое файла
     */
    protected string $content;
    /**
     * Размер файла
     */
    public int $size;
    /**
     * Mime тип файла
     */
    public string $type;
    /**
     * Имя файла
     */
    public string $name;
    /**
     * Оригинальное имя файла
     */
    protected string $sysName;
    /**
     * Расширение файла
     */
    protected string $extension;
    /**
     * Http заголовки ссылки
     */
    protected array $headers;

    /**
     * Конструктор файла по ссылке
     */
    public function __construct(string $link, array $config = [])
    {
        $this->link = $link;

        parent::__construct($config);
    }

    /**
     * Инициализация базовых параметров файла
     */
    public function init(): void
    {
        parent::init();

        $this->setName($this->getFileNameFromLink());
        $this->setType($this->getMimeTypeFromLink());
        $this->setSize($this->getFileSizeFormLink());
        if (empty($this->getExtension())) {
            $ext = FileHelper::getExtensionsByMimeType($this->getType());
            $this->setName($this->getName() . '.' . array_pop($ext));
        }
    }

    /**
     * Конфигурация компонента
     *
     * @throws InvalidConfigException
     */
    public function configure($config): void
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
        $this->initListener();
    }

    /**
     * Инициализация одной модели
     */
    public static function getInstance($model, $attribute): UploadInterface
    {
        return self::getInstanceByName($model->{$attribute});
    }

    /**
     * Инициализация массива моделей
     *
     * @return UploadInterface[]
     */
    public static function getInstances($model, $attribute): array
    {
        if (is_string($attribute)) {
            $attribute = [$attribute];
        }

        return array_map(static fn (string $attribute) => self::getInstance($model, $attribute), $attribute);
    }

    /**
     * Инициализация одной модели по имени атрибута
     */
    public static function getInstanceByName($name): UploadInterface
    {
        return new self($name);
    }

    /**
     * Инициализация массива моделей по имени атрибута
     *
     * @return UploadInterface[]
     */
    public static function getInstancesByName($name): array
    {
        if (is_array($name) === false) {
            return [new self($name)];
        }

        return array_map(static fn (string $link) => self::getInstancesByName($link), $name);
    }

    /**
     * Сохранение файла
     *
     * @throws NotUploadFileException
     */
    public function saveAs($file, $deleteTempFile = false): bool
    {
        if ($this->beforeSave($file, $deleteTempFile)) {
            return file_put_contents($file, $this->getContent());
        }
        return false;
    }

    /**
     * Удаление файла
     */
    public function deleteFile(string $filePath, ?Throwable $exception = null): bool
    {
        return $this->beforeDelete($filePath, $exception);
    }

    /**
     * Получить файл
     */
    public function getFile(): string
    {
        return $this->link;
    }

    /**
     * Получить файл
     *
     * @throws NotUploadFileException
     */
    public function getContent(): string
    {
        if (empty($this->content)) {
            $this->content = file_get_contents($this->link);
            if ($this->content === false) {
                throw new NotUploadFileException('Ошибка чтения контента по ссылке: ' . $this->link);
            }
        }
        return $this->content;
    }

    /**
     * Получение информации об оригинальном именовании файла
     */
    public function getBaseName(): string
    {
        $pathInfo = pathinfo('_' . basename($this->getName()), PATHINFO_FILENAME);
        return mb_substr($pathInfo, 1, mb_strlen($pathInfo, '8bit'), '8bit');
    }

    /**
     * Получение расширения файла
     */
    public function getExtension(): string
    {
        return strtolower(pathinfo(basename($this->getName()), PATHINFO_EXTENSION));
    }

    /**
     * Установить расширение файла
     */
    public function setExtension(string $extension): void
    {
        $this->setName($this->getBaseName() . '.' . $extension);
    }

    /**
     * Получение полного имени файла
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Установка полного имени файла
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Получение MIME типа файла
     */
    public function getType(): string
    {
        if (!empty($this->type)) {
            return $this->type;
        }
        if (!empty($this->content) && function_exists('finfo_buffer')) {
            if ($mimeType = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $this->content)) {
                return $mimeType;
            }
        }
        if ($mimeType = $this->getMimeTypeFromLink()) {
            return $mimeType;
        }
        return 'text/plain';
    }

    /**
     * Установить mime тип файла
     */
    public function setType(string $mime): void
    {
        $this->type = $mime;
    }

    /**
     * Получение размера файла
     */
    public function getSize(): int
    {
        if (!empty($this->size)) {
            return $this->size;
        }
        if (!empty($this->content)) {
            return $this->size = strlen($this->content);
        }
        if ($length = $this->getFileSizeFormLink()) {
            return $this->size = $length;
        }
        return 0;
    }

    /**
     * Установить размер файла
     */
    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    /**
     * Получить системное имя файла
     */
    public function getSysName(): string
    {
        if (empty($this->sysName)) {
            $this->sysName = uniqid();
        }
        return $this->sysName . '.' . $this->getExtension();
    }

    /**
     * Установить системное имя
     */
    public function setSysName(string $sysName): void
    {
        $this->sysName = $sysName;
    }

    /**
     * Получить mime тип по ссылке
     */
    protected function getMimeTypeFromLink(): ?string
    {
        return $this->getHeaderContent('Content-Type');
    }

    /**
     * Получить размер файла по ссылке
     */
    protected function getFileSizeFormLink(): ?string
    {
        return $this->getHeaderContent('Content-Length');
    }

    /**
     * Получить имя файла по ссылке
     */
    protected function getFileNameFromLink(): ?string
    {
        $header = $this->getHeaderContent('Content-Disposition');
        if (preg_match('/filename=\"([^\"]*)\";/sui', $header, $match)) {
            return trim($match[1]);
        }
        $info = parse_url($this->link);
        if (!empty($info['path'])) {
            return basename($info['path']);
        }
        return uniqid();
    }

    /**
     * Получить содержимое нужного заголовка
     */
    protected function getHeaderContent(string $name): ?string
    {
        if (empty($this->headers) && $headers = get_headers($this->link)) {
            foreach ($headers as $header) {
                $items = explode(':', $header);
                if (count($items) == 2) {
                    [$name, $value] = explode(':', $header);
                    $this->headers[$name] = trim($value);
                }
            }
        }
        if (!empty($this->headers[$name])) {
            return $this->headers[$name];
        }
        return null;
    }

    /**
     * Необходимость удаление временного файла после загрузки
     */
    public function needDeleteTempFile(): bool
    {
        return false;
    }
}
