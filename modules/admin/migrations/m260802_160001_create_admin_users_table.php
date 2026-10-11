<?php

use yii\db\Migration;

/**
 * Таблицы admin_users / admin_login_log и учётная запись admin/admin.
 *
 * Вызывается из migrations/m260802_160000_create_admin_users_table.php.
 */
class m260802_160001_create_admin_users_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%admin_users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(64)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'role' => $this->smallInteger()->notNull()->comment('100=admin, 50=manager'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'last_login_at' => $this->integer()->null(),
            'last_login_ip' => $this->string(45)->null(),
        ], $tableOptions);

        $this->createIndex('idx-admin_users-username', '{{%admin_users}}', 'username', true);

        $logTableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $logTableOptions = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
        }

        $this->createTable('{{%admin_login_log}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'admin_user_id' => $this->integer()->null()->comment('NULL если учётка удалена'),
            'username' => $this->string(64)->notNull()->comment('Снимок логина на момент входа'),
            'ip' => $this->string(45)->null(),
            'user_agent' => $this->string(512)->null(),
            'created_at' => $this->integer()->notNull(),
        ], $logTableOptions);

        $this->createIndex('idx_admin_login_log_user', '{{%admin_login_log}}', 'admin_user_id');
        $this->createIndex('idx_admin_login_log_created', '{{%admin_login_log}}', 'created_at');

        $this->addForeignKey(
            'fk_admin_login_log_user',
            '{{%admin_login_log}}',
            'admin_user_id',
            '{{%admin_users}}',
            'id',
            'SET NULL',
            'CASCADE',
        );

        $now = time();
        $this->insert('{{%admin_users}}', [
            'username' => 'admin',
            'auth_key' => Yii::$app->security->generateRandomString(),
            'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
            'role' => 100,
            'status' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_admin_login_log_user', '{{%admin_login_log}}');
        $this->dropTable('{{%admin_login_log}}');
        $this->dropTable('{{%admin_users}}');
    }
}
