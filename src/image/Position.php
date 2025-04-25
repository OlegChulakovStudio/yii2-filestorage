<?php
/**
 * Файл класса Position
 *
 * @copyright Copyright (c) 2018, Oleg Chulakov Studio
 * @link http://chulakov.com/
 */

namespace chulakov\filestorage\image;

class Position
{
    /**
     * Позиционирование от левого верхнего края
     */
    public const TOP_LEFT = 'top-left';
    /**
     * Позиционирование от верха
     */
    public const TOP = 'top';
    /**
     * Позиционирование от верхнего правого края
     */
    public const TOP_RIGHT = 'top-right';
    /**
     * Позиционирование от левого края
     */
    public const LEFT = 'left';
    /**
     * Позиционирование от ценрта (по-умолчанию)
     */
    public const CENTER = 'center';
    /**
     * Позиционирование от правого края
     */
    public const RIGHT = 'right';
    /**
     * Позиционирование от нижнего левого края
     */
    public const BOTTOM_LEFT = 'bottom-left';
    /**
     * Позиционирование от нижнего края
     */
    public const BOTTOM = 'bottom';
    /**
     * Позиционирование от нижнего правого края
     */
    public const BOTTOM_RIGHT = 'bottom-right';
}
