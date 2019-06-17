<?php

namespace marqu3s\behaviors\activeRecord\models;

use common\models\rh\FuncionarioNasajon;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "log_active_record".
 *
 * @property integer $id
 * @property string $model_class
 * @property string $model_id
 * @property string $log
 * @property string $created_by
 * @property string $created_at
 */
class LogActiveRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log_active_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_class', 'model_id', 'log', 'created_by'], 'required'],
            [['log'], 'string'],
            [['created_at'], 'safe'],
            [['model_class'], 'string', 'max' => 255],
            [['created_by'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_class' => 'Model Class',
            'model_id' => 'Model ID',
            'log' => 'Log',
            'created_by' => 'Created by',
            'created_at' => 'Created at',
        ];
    }

    /**
     * Get all the logs for a model with the specified ID.
     *
     * @param ActiveRecord $model
     * @param string|int|null $modelId
     *
     * @return LogActiveRecord[]
     */
    public static function getLogs($model, $modelId = null)
    {
        if (empty($modelId)) {
            $modelId = $model->getPrimaryKey();
        }

        $modelClass = get_class($model);

        return self::find()
            ->where(['model_class' => $modelClass, 'model_id' => $modelId])
            ->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC])
            ->all();
    }
}
