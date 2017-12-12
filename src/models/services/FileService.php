<?php
/**
 * Файл класса FileService.php
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models\services;

use chulakov\filestorage\models\Image;
use chulakov\filestorage\models\repositories\FileRepository;
use chulakov\filestorage\models\File;
use yii\web\UploadedFile;

class FileService
{
    /** @var FileRepository */
    private $repository;

    public function __construct(FileRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param array $config
     * @return File|null
     * @throws \Exception
     */
    public function createFile(UploadedFile $uploadedFile, array $config = [])
    {
        $file = new File($uploadedFile, $config);
        return $this->repository->save($file) ? $file : null;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param array $config
     * @return Image|null
     * @throws \Exception
     */
    public function createImage(UploadedFile $uploadedFile, array $config = [])
    {
        $file = new Image($uploadedFile, $config);
        return $this->repository->save($file) ? $file : null;
    }

    public function update()
    {

    }

    public function delete()
    {

    }
}