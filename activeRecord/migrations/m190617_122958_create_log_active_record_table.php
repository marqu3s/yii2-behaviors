<?php

use yii\db\Migration;

/**
 * Handles the creation of table `log_active_record`.
 */
class m190617_122958_create_log_active_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('log_active_record', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'model_class' => $this->string(255)->notNull(),
            'model_id' => $this->string(50)->notNull(),
            'log' => $this->text()->notNull(),
            'created_by' => $this->string(50)->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('log_active_record');
    }
}
