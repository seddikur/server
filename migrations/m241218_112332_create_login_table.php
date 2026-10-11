<?php

use yii\db\Migration;

/**
 * Делегирует таблицу login_record модулю users.
 */
class m241218_112332_create_login_table extends Migration
{
    public function safeUp(): void
    {
        require_once __DIR__ . '/../modules/users/migrations/m241218_112333_create_login_record_table.php';
        (new m241218_112333_create_login_record_table(['db' => $this->db]))->safeUp();
    }

    public function safeDown(): void
    {
        require_once __DIR__ . '/../modules/users/migrations/m241218_112333_create_login_record_table.php';
        (new m241218_112333_create_login_record_table(['db' => $this->db]))->safeDown();
    }
}
