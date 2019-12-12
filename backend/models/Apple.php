<?php

namespace backend\models;

use common\models\Apple as AppleCommon;
use Yii;

/**
 * Замечание:
 * Обычно в common находятся только базовые модели, не наполненные глубоким смыслом.
 * Часто встречаю такую ситуацию, когда базовые модели очень "жирные": в них сложно разобраться, они содержат много ненужных зависимостей.
 * Чтобы этого избежать лучше "дробить" базовую модель, наследуя её в модулях. Такое бывает редко, но иногда в разных модулях в итоге вообще получаются модели для одной и той же сущности, не имеющие общего предка.
 */
class Apple extends AppleCommon
{
    /** @var int Произвольная дата создания яблока: минимальный UNIXTIME */
    public const RAND_CREATED_AT_FROM = 1572566400;
    /** @var int Произвольная дата создания яблока: максимальный UNIXTIME */
    public const RAND_CREATED_AT_TO = 1575244799;
    /** @var int Минимальный срок (от настоящего момента) для определения когда упало яблоко */
    public const RAND_FALL_PERIOD_MIN = 0;
    /** @var int Максимальный срок (от настоящего момента) для определения когда упало яблоко */
    public const RAND_FALL_PERIOD_MAX = 3600 * 10;

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
     * Период, сколько яблоко уже лежит на земле
     *
     * @return string
     */
    public function fallPeriod(): string
    {
        if ($this->onGround === false) {
            return '';
        }
        return self::_timeToHi(time() - strtotime($this->fall_date));
    }

    /**
     * Период с того момента, как яблоко испортилось
     *
     * @return string
     */
    public function rotterPeriod(): string
    {
        if ($this->rotten === false) {
            return '';
        }
        return self::_timeToHi(time() - strtotime($this->fall_date) - self::ROTTEN_PERIOD);
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

    private static function _timeToHi(int $time): string
    {
        $h = floor($time / 3600);
        $m = round(($time - $h * 3600) / 60);
        return
            Yii::$app->i18n->format('{n, plural, =0{} =1{1 час} one{# час} few{# часа} many{# часов} other{# часов}}',
                ['n' => $h], 'ru_RU') .
            Yii::$app->i18n->format('{n, plural, =0{} =1{ 1 минуту} one{ # минуту} few{ # минуты} many{ # минут} other{ # минут}}',
                ['n' => $m], 'ru_RU');

    }

}