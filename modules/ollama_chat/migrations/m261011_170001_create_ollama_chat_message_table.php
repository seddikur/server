<?php

use yii\db\Migration;

/**
 * История чата Ollama (пользователи сайта, таблица user).
 */
class m261011_170001_create_ollama_chat_message_table extends Migration
{
    private const TABLE_NAME = 'ollama_chat_message';

    public function safeUp(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Сообщения чата Ollama"';
        }

        $this->createTable(self::TABLE_NAME, [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('FK user.id'),
            'role' => $this->string(20)->notNull()->comment('user|assistant|system'),
            'content' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-ollama_chat_message-user_id', self::TABLE_NAME, 'user_id');
        $this->createIndex('idx-ollama_chat_message-user_id-id', self::TABLE_NAME, ['user_id', 'id']);
    }

    public function safeDown(): void
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
