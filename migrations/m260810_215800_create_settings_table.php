<?php

use yii\db\Migration;

/**
 * Делегирует таблицу settings модулю setting.
 */
class m260810_215800_create_settings_table extends Migration
{
    public function safeUp(): void
    {
        require_once __DIR__ . '/../modules/setting/migrations/m260810_215801_create_settings_table.php';
        (new m260810_215801_create_settings_table(['db' => $this->db]))->safeUp();
    }

    public function safeDown(): void
    {
        require_once __DIR__ . '/../modules/setting/migrations/m260810_215801_create_settings_table.php';
        (new m260810_215801_create_settings_table(['db' => $this->db]))->safeDown();
    }
}
