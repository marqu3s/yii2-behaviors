<?php

use yii\db\Migration;

/**
 * Handles the update of table `log_active_record`.
 */
class m190729_105958_update_log_active_record_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('log_active_record', 'created_by', 'string(100) NOT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
