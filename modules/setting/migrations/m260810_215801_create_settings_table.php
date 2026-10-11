<?php

use yii\db\Migration;

/**
 * Таблица settings и начальные ключи (Яндекс.Метрика, GeoIP).
 *
 * Подключается из migrations/m260810_215800_create_settings_table.php.
 */
class m260810_215801_create_settings_table extends Migration
{
    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%settings}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string(255)->notNull()->unique(),
            'value' => $this->text()->null(),
            'description' => $this->string(512)->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $now = date('Y-m-d H:i:s');

        $this->batchInsert('{{%settings}}', ['key', 'value', 'description', 'created_at', 'updated_at'], [
            [
                'yandex_metrika_counter',
                '',
                'ID счётчика Яндекс.Метрики (число, без скрипта)',
                $now,
                $now,
            ],
            [
                'yandex_webmaster_verification',
                '',
                'Код подтверждения Яндекс.Вебмастера: content из meta name="yandex-verification" (вставьте только значение content, без тега)',
                $now,
                $now,
            ],
            [
                'geoip_enabled',
                '0',
                'Включить определение региона по IP (GeoIP2 MaxMind)',
                $now,
                $now,
            ],
            [
                'geoip_db_path',
                'geoip/GeoLite2-City.mmdb',
                'Путь к файлу базы GeoLite2 относительно @app/runtime/',
                $now,
                $now,
            ],
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%settings}}');
    }
}
