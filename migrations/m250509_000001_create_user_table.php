<?php

use yii\db\Migration;

/**
 * Делегирует таблицу user модулю users.
 */
class m250509_000001_create_user_table extends Migration
{
    public function safeUp(): void
    {
        require_once __DIR__ . '/../modules/users/migrations/m250509_000002_create_user_table.php';
        (new m250509_000002_create_user_table(['db' => $this->db]))->safeUp();
    }

    public function safeDown(): void
    {
        require_once __DIR__ . '/../modules/users/migrations/m250509_000002_create_user_table.php';
        (new m250509_000002_create_user_table(['db' => $this->db]))->safeDown();
    }
}
