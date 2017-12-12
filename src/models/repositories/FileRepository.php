<?php
/**
 * Файл класса FileRepository.php
 *
 * @copyright Copyright (c) 2017, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models\repositories;

use chulakov\filestorage\models\BaseFile;

class FileRepository
{
    /**
     * @param BaseFile $file
     * @return bool
     * @throws \Exception
     */
    public function save(BaseFile $file)
    {
        if (!$file->save()) {
            throw new \Exception('Модель file не сохранена.');
        }

        return true;
    }
}