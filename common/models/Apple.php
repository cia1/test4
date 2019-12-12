<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use Throwable;

/**
 * "Яблоко"
 * @property int             $id         Первичный ключ
 * @property string          $color      RGB-цвет
 * @property int             $eaten      Сколько съедено (процент)
 * @property string          $created_at Дата и время создания (появления)
 * @property string|int|null $fall_date  Дата и время падения
 *
 * @property int             $size       Сколько процентов осталось (ещё не съедено)
 * @property int             $status     Состояние яблока (@see self::STATUS)
 * @property bool            $onTree     Яблоко всё ещё висит на дереве
 * @property bool            $onGround   Яблоко лежит на земле
 * @property bool            $rotten     Яблоко испортилось
 *
 */
class Apple extends ActiveRecord
{

    public $tmp;

    //Состояние яблока
    public const STATUS = [
        self::STATUS_ON_TREE,
        self::STATUS_ON_GROUND,
    ];
    public const STATUS_ON_TREE = 1; //висит на дереве
    public const STATUS_ON_GROUND = 2; //лежит на земле

    protected const ROTTEN_PERIOD = 3600 * 5; //Срок, пока яблоко не испортится (5 часов)

    /** @inheritDoc */
    public static function tableName(): string
    {
        return 'apple';
    }

    /** @inheritDoc */
    public function rules(): array
    {
        return [
            ['eaten', 'default', 'value' => 0],
            ['color', 'required'],
            ['color', 'filter', 'filter' => 'strtoupper'],
            ['color', 'match', 'pattern' => '/^[0-9A-Z]{6}$/'],
            ['eaten', 'integer', 'min' => 0, 'max' => 100],
            [
                'fall_date',
                'filter',
                'filter' => function ($value) {
                    if (is_int($value) === true) {
                        $value = date('Y-m-d H:i:s', $value);
                    }
                    return $value;
                },
            ],
            [['created_at', 'fall_date'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'color' => 'Цвет',
            'eaten' => 'Съедено',
            'created_at' => 'Появилось',
            'fall_date' => 'Упало',
        ];
    }

    /**
     * "Уронить" яблоко
     *
     * @return bool Удалось ли сбросить яблоко на землю
     * @throws Throwable
     */
    public function fall(): bool
    {
        if ($this->fall_date !== null) {
            $this->addError('fall_date', 'Яблоко уже на земле');
            return false;
        }
        $this->fall_date = date('Y-m-d H:i:s');
        return true;
    }

    /**
     * Сколько осталось (%)
     *
     * @return int
     */
    public function getSize(): int
    {
        return 100 - $this->eaten;
    }

    /**
     * "Откусить" от яблока
     *
     * @param int $percent Процент, который нужно "откусить"
     * @return bool Удалось ли откусить
     * @throws Throwable
     */
    public function eat(int $percent): bool
    {
        if ($this->fall_date === null) {
            $this->addError('eaten', 'Яблоко всё ещё висит на дереве: откусить нельзя');
            return false;
        }
        if (time() - strtotime($this->fall_date) > self::ROTTEN_PERIOD) {
            $this->addError('eaten', 'Яблоко испортилось, кушать нельзя');
        }
        if ($percent > $this->size) {
            $this->addError('eaten', 'Можно откусить не более ' . $this->size . '%');
            return false;
        }
        $this->eaten += $percent;
        return true;
    }

    /**
     * Съесть всё яблоко
     *
     * @return bool Удалось ли съесть яблоко
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function eatAll(): bool
    {
        return $this->eat($this->size) && $this->save();
    }

    /**
     * Состояние яблока
     *
     * @return int @see self::STATUS
     */
    public function getStatus(): int
    {
        return $this->fall_date === null ? self::STATUS_ON_TREE : self::STATUS_ON_GROUND;
    }

    /**
     * Висит ли яблоко на дереве
     *
     * @return bool
     */
    public function getOnTree(): bool
    {
        return $this->fall_date === null;
    }

    /**
     * Лежит ли яблоко на земле
     *
     * @return bool
     */
    public function getOnGround(): bool
    {
        return $this->fall_date !== null;
    }

    /**
     * Испортилось ли яблоко
     *
     * @return bool
     */
    public function getRotten(): bool
    {
        if ($this->fall_date === null) {
            return false;
        }
        return time() - strtotime($this->fall_date) > self::ROTTEN_PERIOD;
    }

    /**
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool|false|int
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->eaten == 100) {
            return $this->delete();
        }
        return parent::save($runValidation, $attributeNames);
    }

}