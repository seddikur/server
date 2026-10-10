<?php

use yii\db\Migration;

/**
 * Таблицы settings / seo_settings + начальные ключи (Яндекс.Метрика, GeoIP, SEO).
 */
class m260810_215800_create_settings_and_seo_tables extends Migration
{
    public function safeUp()
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

        $this->createTable('{{%seo_settings}}', [
            'id' => $this->tinyInteger()->unsigned()->notNull(),
            'site_name' => $this->string(255)->notNull(),
            'title_separator' => $this->string(32)->notNull()->defaultValue('|'),
            'title_default' => $this->string(512)->notNull(),
            'title_template' => $this->string(512)->notNull(),
            'description_default' => $this->text()->notNull(),
            'description_template' => $this->text()->null(),
            'keywords' => $this->text()->null(),
            'og_image' => $this->string(1024)->null(),
            'indexable' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('pk_seo_settings', '{{%seo_settings}}', 'id');

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

        $this->insert('{{%seo_settings}}', [
            'id' => 1,
            'site_name' => 'Wind Monitor',
            'title_separator' => '|',
            'title_default' => 'Wind Monitor — мониторинг ветра',
            'title_template' => '{page} {sep} {site}',
            'description_default' => 'Мониторинг ветра с сети метеостанций: скорость, направление, порывы. Данные с ESP8266 в реальном времени.',
            'description_template' => '{page}. {site}.',
            'keywords' => 'ветер, метеостанция, анемометр, мониторинг ветра, ESP8266, скорость ветра',
            'og_image' => null,
            'indexable' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%seo_settings}}');
        $this->dropTable('{{%settings}}');
    }
}
