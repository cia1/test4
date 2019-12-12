<?php

namespace common\models;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\Html;
use Throwable;

/**
 * Базовый класс формы, для управления подчинённой моделью
 */
abstract class ModelManagerForm extends Model
{

    /** @var ActiveRecord */
    public $model;

    /**
     * Должен вернуть имя класса модели
     *
     * @return string
     */
    abstract public static function modelClass(): string;

    /**
     * Были ли ошибки при валидации формы, а также связанной модели
     *
     * @param null $attribute
     * @return bool
     */
    public function hasErrors($attribute = null)
    {
        if (parent::hasErrors($attribute) === true) {
            return true;
        }
        if ($this->model instanceof Model) {
            return $this->model->hasErrors($attribute);
        }
        return false;
    }

    /**
     * Объединяет ошибки формы и модели
     *
     * @param bool $showAllErrors
     * @return array
     */
    public function getErrorSummary($showAllErrors)
    {
        $errors = parent::getErrorSummary($showAllErrors);
        if (($showAllErrors || count($errors) === 0) && $this->model instanceof Model) {
            $errors = array_merge($this->model->getErrorSummary($showAllErrors));
        }
        return $errors;
    }

    /**
     * Возвращает массив ошибок для AJAX-валидации
     *
     * @param string $attribute
     * @return array
     */
    public function summaryAjaxErrors(string $attribute = 'id')
    {
        $errors = [];
        foreach ($this->getErrors() as $error) {
            $errors = array_merge($errors, $error);
        }
        if ($this->model instanceof Model) {
            foreach ($this->model->getErrors() as $error) {
                $errors = array_merge($errors, $error);
            }
        }
        return [
            Html::getInputId($this, $attribute) => $errors,
        ];
    }

    /**
     * Удаляет связанную модель
     *
     * @return int|false
     * @throws StaleObjectException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function delete()
    {
        if ($this->validate() === false) {
            return false;
        }
        if ($this->findModel() === false) {
            return false;
        }
        $result = $this->model->delete();
        if ($result) {
            $this->model = null;
        }
        return $result;
    }

    /**
     * Загружает данные связанной модели. Вызывает findModelFail(), если найти модель не удалось
     *
     * @return bool Удалось ли загрузить данные
     * @throws InvalidConfigException
     */
    public function findModel(): bool
    {
        /** @var ActiveRecord $model */
        $model = static::modelClass();
        $attribute = $model::primaryKey()[0];
        if ($this->hasProperty($attribute) === false) {
            throw new InvalidConfigException('MadelManager has no attribute for model\'s primary key');
        }
        $this->model = $model::findOne([$attribute => $this->$attribute]);
        if ($model === null) {
            return $this->findModelFail();
        }
        return true;
    }

    /**
     * Вызывается если модель не найдена. Переопределите этот метод, чтобы, например, выбросить исключение
     *
     * @return bool
     */
    protected function findModelFail(): bool
    {
        return false;
    }
}