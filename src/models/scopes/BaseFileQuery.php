<?php
/**
 * Файл класса BaseFileQuery
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\models\scopes;

use yii\db\ActiveQuery;

/**
 * Класс выборка для модели файлов
 *
 * @package chulakov\filestorage\models\scopes
 */
class BaseFileQuery extends ActiveQuery
{
    /**
     * Выборка по идентификатору
     *
     * @param integer $id
     * @return $this
     */
    public function findById($id)
    {
        return $this->andWhere([$this->getPrimaryTableName() . '.[[id]]' => $id]);
    }

    /**
     * Выборка по коду группы
     *
     * @param string $group
     * @return $this
     */
    public function findByGroup($group)
    {
        return $this->andWhere([$this->getPrimaryTableName() . '.[[group_code]]' => $group]);
    }

    /**
     * Выборка по идентификатору объекта
     *
     * @param integer $objectId
     * @return $this
     */
    public function findByObjectId($objectId)
    {
        return $this->andWhere([$this->getPrimaryTableName() . '.[[object_id]]' => $objectId]);
    }

    /**
     * Выборка по типу объекта
     *
     * @param string $objectType
     * @return $this
     */
    public function findByObjectType($objectType)
    {
        return $this->andWhere([$this->getPrimaryTableName() . '.[[object_type]]' => $objectType]);
    }

    /**
     * @inheritdoc
     */
    public function one($db = null)
    {
        $this->limit(1);
        return parent::one($db);
    }
}
