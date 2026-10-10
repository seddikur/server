<?php

use yii\db\Migration;

/**
 * Создается таблица «Пользователи» (клиенты сайта).
 */
class m250509_000001_create_user_table extends Migration
{
    const TABLE_NAME = 'user';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Пользователи"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'first_name' => $this->string(64)->notNull()->comment('Имя'),
            'last_name' => $this->string(64)->notNull()->comment('Фамилия'),
            'email' => $this->string(128)->notNull()->unique()->comment('Email'),
            'phone' => $this->string(32)->notNull()->unique()->comment('Телефон (логин)'),
            'password_hash' => $this->string()->notNull()->comment('Хеш пароля'),
            'auth_key' => $this->string(32)->comment('Ключ авторизации'),
            'status' => $this->smallInteger()->notNull()->defaultValue(0)->comment('Статус'),
            'created_at' => $this->integer()->notNull()->comment('Дата создания'),
            'updated_at' => $this->integer()->notNull()->comment('Дата обновления'),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
