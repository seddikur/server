<?php

use yii\db\Migration;

/**
 * Делегирует создание admin_users / admin_login_log модулю admin.
 */
class m260802_160000_create_admin_users_table extends Migration
{
    public function safeUp(): void
    {
        require_once __DIR__ . '/../modules/admin/migrations/m260802_160001_create_admin_users_table.php';
        (new m260802_160001_create_admin_users_table(['db' => $this->db]))->safeUp();
    }

    public function safeDown(): void
    {
        require_once __DIR__ . '/../modules/admin/migrations/m260802_160001_create_admin_users_table.php';
        (new m260802_160001_create_admin_users_table(['db' => $this->db]))->safeDown();
    }
}
