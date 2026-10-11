<?php

use yii\db\Migration;

/**
 * Делегирует таблицу ollama_chat_message модулю ollama_chat.
 */
class m261011_170000_create_ollama_chat_message_table extends Migration
{
    public function safeUp(): void
    {
        require_once __DIR__ . '/../modules/ollama_chat/migrations/m261011_170001_create_ollama_chat_message_table.php';
        (new m261011_170001_create_ollama_chat_message_table(['db' => $this->db]))->safeUp();
    }

    public function safeDown(): void
    {
        require_once __DIR__ . '/../modules/ollama_chat/migrations/m261011_170001_create_ollama_chat_message_table.php';
        (new m261011_170001_create_ollama_chat_message_table(['db' => $this->db]))->safeDown();
    }
}
