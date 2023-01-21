<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%article}}`.
 */
class m230119_192418_create_article_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%article}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'description' => $this->text(),
            'content' => $this->text(),
            'date' => $this->date(),
            'image' => $this->string(),
            'viewed' => $this->integer()->defaultValue(0),
            'category_id' => $this->integer(),
            'user_id' => $this->integer(),
        ]);


        $this->createIndex(
            'idx-category_id',
            'article',
            'category_id'
        );

        $this->addForeignKey(
            'fk-category_id',
            'article',
            'category_id',
            'category',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-post-user_id',
            'article',
            'user_id'
        );

        $this->addForeignKey(
            'fk-post-user_id',
            'article',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%article}}');
    }
}
