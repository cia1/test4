<?php

use common\models\User;
use yii\db\Migration;
use yii\base\Exception;

/**
 * Создаёт демо-пользователя с логином "root" и паролем "masterkey"
 */
class m191211_091434_user_insert_root extends Migration
{
    const USER_NAME='root';
    const PASSWORD='masterkey';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->insert('user', [
            'username' => 'root',
            'auth_key' => 'uf24dfj5mLhmbKtyeQFG7fjOGincBpED',
            'password_hash' => Yii::$app->getSecurity()->generatePasswordHash(self::PASSWORD),
            'email' => 'root@example.com',
            'status' => User::STATUS_ACTIVE,
            'verification_token' => '_bqU40YN1LEoz6Kf1R7OnvwsFH6iGpyl_1576055541',
            'created_at' => time(),
            'updated_at' => time()
        ]);

    }

    public function down()
    {
        $this->delete('user', ['username' => 'root']);
    }

}
