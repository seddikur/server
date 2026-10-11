<?php

use yii\db\Migration;

/**
 * Таблица login_record — журнал входов на сайт.
 */
class m241218_112333_create_login_record_table extends Migration
{
    private const TABLE_NAME = 'login_record';

    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "login_record"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->comment('Логин'),
            'clientIp' => $this->string(64)->comment('Ip'),
            'created_at' => $this->datetime()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата и время'),
        ], $tableOptions);

        $this->createIndex('idx_login_record_created', self::TABLE_NAME, 'created_at');
    }

    public function safeDown(): void
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
