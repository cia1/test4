<?php

namespace backend\models;

use common\models\ModelManagerForm;
use yii\db\StaleObjectException;
use Throwable;

/**
 * Форма для управления яблоками. Управляемая модель находится в $this->>model.
 * Замечание:
 * Из этой полезно выделить  общую составляющую в отдельный базовый класс,
 * тогда эта "форма" будет применима для управления любыми другими моделями, именно поэтому в некоторых методах "instanceof" сверяет с базовыми классами.
 * Однако для данного конкретного простого случая увеличивать глубину вложенности классов не имеет смысла.
 */
class AppleActionForm extends ModelManagerForm
{

    /** @var Apple */
    public $model;

    /** @inheritDoc */
    public static function modelClass(): string
    {
        return Apple::class;
    }

    /** @var int ID модели (яблока) */
    public $id;
    /** @var int Количество для создания моделей */
    public $quantity;
    /** @var int Количество для "откусить" */
    public $percent;
    /** @var bool Сбросить некоторое количество яблок на землю */
    public $onGround;

    public const SCENARIO_CREATE = 'create'; //добавить яблоки
    public const SCENARIO_FALL = 'fall'; //сбросить на землю
    public const SCENARIO_EAT = 'eat'; //откусить
    public const SCENARIO_DELETE = 'delete'; //удалить

    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['quantity', 'onGround'],
            self::SCENARIO_FALL => ['id'],
            self::SCENARIO_EAT => ['id', 'percent'],
            self::SCENARIO_DELETE => ['id'],
        ];
    }

    public function rules(): array
    {
        return [
            [['id', 'quantity', 'percent'], 'required'],
            [
                'onGround',
                'filter',
                'filter' => function ($value) {
                    return (bool)$value;
                },
            ],
            ['id', 'integer'],
            ['id', 'exist', 'targetClass' => Apple::class],
            ['quantity', 'integer', 'min' => 1, 'max' => 1000],
            ['percent', 'integer', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'quantity' => 'Количество',
            'percent' => 'Количество (%)',
            'onGround' => 'Сорвать несколько',
        ];
    }

    /**
     * Создаёт модели инициируя их произвольными данными
     *
     * @return int Количество реально созданных моделей
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function create(): int
    {
        if ($this->validate() === false) {
            return false;
        }
        for ($i = 0; $i < $this->quantity; $i++) {
            $apple = Apple::instanceRand();
            if ($this->onGround === true && rand(0, 1) === 1) {
                $time = time();
                $apple->fall_date = rand($time - $apple::RAND_FALL_PERIOD_MIN, $time - $apple::RAND_FALL_PERIOD_MAX);
            }
            if ($apple->save() === false) {
                $this->addErrors($apple->getFirstErrors());
                return $i + 1;
            }
        }
        return $i;
    }

    /**
     * "Сорвать"
     *
     * @return bool Успешно ли сорвано яблоко
     * @throws Throwable
     */
    public function fall(): bool
    {
        if ($this->validate() === false) {
            return false;
        }
        $this->findModel();
        return $this->model->fall();
    }

    /**
     * "Откусить"
     * @return bool Успешно ли откушен кусочек яблока
     * @throws Throwable
     */
    public function eat(): bool
    {
        if ($this->validate() === false) {
            return false;
        }
        $this->findModel();
        return $this->model->eat($this->percent);
    }

}