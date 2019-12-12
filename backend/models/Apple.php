<?php

namespace backend\models;

use common\models\Apple as AppleCommon;

/**
 * Замечание:
 * Обычно в common находятся только базовые модели, не наполненные глубоким смыслом.
 * Часто встречаю такую ситуацию, когда базовые модели очень "жирные": в них сложно разобраться, они содержат много ненужных зависимостей.
 * Чтобы этого избежать лучше "дробить" базовую модель, наследуя её в модулях. Такое бывает редко, но иногда в разных модулях в итоге вообще получаются модели для одной и той же сущности, не имеющие общего предка.
 */
class Apple extends AppleCommon
{
    /** @var int Произвольная дата создания яблока: минимальный UNIXTIME */
    const RAND_CREATED_AT_FROM = 1572566400;
    /** @var int Произвольная дата создания яблока: максимальный UNIXTIME */
    const RAND_CREATED_AT_TO = 1575244799;

    /**
     * Создаёт модель инициируя её произвольными данными
     *
     * @return Apple
     */
    public static function instanceRand(): self
    {
        $apple = new self();
        $apple->color = self::randColor();
        $apple->created_at = self::randCreatedAt();
        return $apple;
    }

    /**
     * Возвращает произвольный цвет
     *
     * @return string RGB-строка [0-9A-F]{6}
     */
    protected static function randColor(): string
    {
        return str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Возвращает произвольную дату создания
     *
     * @return string
     */
    protected static function randCreatedAt(): string
    {
        return date('Y-m-d H:i:s', rand(self::RAND_CREATED_AT_FROM, self::RAND_CREATED_AT_TO));
    }

}