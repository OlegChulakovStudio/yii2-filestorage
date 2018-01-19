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
    const TOP_LEFT = 'top-left';
    /**
     * Позиционирование от верха
     */
    const TOP = 'top';
    /**
     * Позиционирование от верхнего правого края
     */
    const TOP_RIGHT = 'top-right';
    /**
     * Позиционирование от левого края
     */
    const LEFT = 'left';
    /**
     * Позиционирование от ценрта (по-умолчанию)
     */
    const CENTER = 'center';
    /**
     * Позиционирование от правого края
     */
    const RIGHT = 'right';
    /**
     * Позиционирование от нижнего левого края
     */
    const BOTTOM_LEFT = 'bottom-left';
    /**
     * Позиционирование от нижнего края
     */
    const BOTTOM = 'bottom';
    /**
     * Позиционирование от нижнего правого края
     */
    const BOTTOM_RIGHT = 'bottom-right';
}