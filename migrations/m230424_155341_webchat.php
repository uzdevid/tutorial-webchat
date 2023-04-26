<?php

use yii\db\Migration;

/**
 * Class m230424_155341_webchat
 */
class m230424_155341_webchat extends Migration {
    /**
     * {@inheritdoc}
     */
    public function safeUp() {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        echo "m230424_155341_webchat cannot be reverted.\n";

        return false;
    }


    public function up() {
        $this->createTable('chat', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'create_time' => $this->bigInteger(16)->notNull(),
        ]);

        $this->createTable('message', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->integer()->notNull(),
            'username' => $this->string(255)->notNull(),
            'text' => $this->text()->notNull(),
            'create_time' => $this->bigInteger(16)->notNull(),
        ]);

        $this->addForeignKey(
            'fk_message_chat_id',
            'message',
            'chat_id',
            'chat',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down(): bool {
        $this->dropTable('message');
        $this->dropTable('chat');

        return true;
    }
}
