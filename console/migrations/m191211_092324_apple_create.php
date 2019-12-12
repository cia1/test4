<?php

use yii\db\Migration;

/**
 * Создание таблицы для сущности "яблоки"
 */
class m191211_092324_apple_create extends Migration
{
    public function up()
    {
        $this->createTable('apple', [
            'id' => $this->primaryKey(10)->unsigned()->notNull(),
            'color' => $this->char(6)->notNull()->comment('Цвет RGB'),
            'eaten' => $this->tinyInteger(3)->unsigned()->notNull()->defaultValue(0)->comment('Сколько съедено (процент)'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата и время добавления (рождения) яблока'),
            'fall_date' => $this->timestamp()->comment('Дата и время падения')
        ], "comment 'Яблоки'");
    }

    public function down()
    {
        $this->dropTable('apple');
    }

}
