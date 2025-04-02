<?php

namespace marqu3s\behaviors\activeRecord;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * LogChangesBehavior automatically logs to the specified table the old and new values
 * of dirty ActiveRecord attributes when updating a record.
 *
 * It can also log when a new record is created and when a record is deleted.
 *
 * For convenience a model for the table containing the logs is provided.
 *
 * Usage: add it to the behaviors() method of your ActiveRecord model and customize it using attributes.
 * Also make sure your model implements LogChangesInterface.
 *
 * For more customization options you can extend this behavior with a class of your own.
 *
 * ```php
 * class MyActiveRecord extends ActiveRecord implements LogChangesInterface <-- Interface is optional
 * {
 *      ...
 *
 *      public function behaviors()
 *      {
 *          return [
 *              'LogChanges' => [
 *                  'class' => LogChangesBehavior::class,
 *                  'valuesReplacement' => [
 *                      'active' => [
 *                          0 => 'No',
 *                          1 => 'Yes',
 *                      ],
 *                      'id_user' => function () {
 *                          return ArrayHelper::map(User::find()->all(), 'id', 'name');
 *                      },
 *                  ],
 *                  'currencyAttributes' => [
 *                      'subtotal', 'total', 'tax'
 *                  ]
 *              ],
 *          ];
 *      }
 * }
 * ```
 *
 * @author Joao Marques <joao@jjmf.com>
 */
class LogChangesBehavior extends Behavior
{
    /**
     * @var string nome da tabela no BD
     */
    public $tableName = 'log_active_record';

    /**
     * @var string name of the column that will keep the related model´s class name.
     */
    public $modelClassColumn = 'model_class';

    /**
     * @var string name of the column that will keep the related model´s ID.
     */
    public $modelIdColumn = 'model_id'; // the ID of the related object

    /**
     * @var string name of the column holding the timestamp of the operation.
     */
    public $dateTimeColumn = 'created_at';

    /**
     * @var string name of the column holding the username that made the operation.
     */
    public $usernameColumn = 'created_by';

    /**
     * @var string name of the column holding the description of all the changes made.
     */
    public $logColumn = 'log';

    /**
     * @var string|\Closure the value to be inserted on the $modelIdColumn column.
     * You can specify a closure.
     * Signature: `function ($model) { return 123; }`. $model will be an instance of LogChangesBehavior attached to the ActiveRecord.
     */
    public $modelIdColumnValue;

    /**
     * @var string|\Closure text to be inserted before the description of the changed attributes.
     * You can specify a closure.
     * Signature: `function ($model) { return 'custom'; }`. $model will be an instance of LogChangesBehavior attached to the ActiveRecord.
     */
    public $textBeforeChanges;

    /**
     * @var string|\Closure text to be inserted after the description of the changed attributes.
     * You can specify a closure.
     * Signature: `function ($model) { return 'custom'; }`. $model will be an instance of LogChangesBehavior attached to the ActiveRecord.
     */
    public $textAfterChanges;

    /**
     * @var string text to log when a new model is saved.
     */
    public $textNewRecord = 'New record created.';

    /**
     * @var string text to log when a model is deleted.
     */
    public $textDeletedRecord = 'Record deleted.';

    /**
     * @var string text used before old value in the changes description.
     */
    public $textChangedFrom = 'changed from';

    /**
     * @var string text used before new value in the changes description.
     */
    public $textChangedTo = 'to';

    /**
     * @var string used to separate changed attributes.
     */
    public $glue = '<br>';

    public $attributeTokenTag = 'span';
    public $oldValueTokenTag = 'span';
    public $newValueTokenTag = 'span';

    public $attributeTokenCssClass = 'label label-default label-changed-value';
    public $oldValueTokenCssClass = 'label label-default label-changed-value';
    public $newValueTokenCssClass = 'label label-default label-changed-value';

    /**
     * @var array text replacement mapping.
     * Can be declared as a closure that don't accept any parameter. It must return an array where
     * keys are the value to be replaced and values are the value to replace.
     * Closures are executed only when the text of the log is generated.
     *
     * Format: attr => ['old value' => 'new value'] | Closure
     * Examples:
     *
     * 'active' => [
     *      '0' => 'No',
     *      '1' => 'Yes',
     * ],
     *
     * 'id_user' => function () {
     *      return ArrayHelper::map(User::find()->all(), 'id', 'name');
     * },
     *
     * In the last example instead of loggin the user ID, the user name will be logged.
     */
    public $valuesReplacement = [];

    /**
     * @var array relation of date only attributes.
     */
    public $dateAttributes = [];

    /**
     * @var array relation of date and time attributes.
     */
    public $dateTimeAttributes = [];

    /**
     * @var array relation of currency attributes.
     */
    public $currencyAttributes = [];

    public $htmlAttributes = [];

    /**
     * @var array relation of ignored attributes. They will not apear on the logs.
     */
    public $ignoredAttributes = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * Create a log entry when a new model is saved.
     *
     * @param $event
     *
     * @throws \yii\db\Exception
     */
    public function afterInsert($event)
    {
        if ($this->modelIdColumnValue === null) {
            $keyColumnValue = $this->owner->getPrimaryKey();
        } elseif (is_string($this->modelIdColumnValue)) {
            $keyColumnValue = $this->modelIdColumnValue;
        } else {
            $keyColumnValue = call_user_func($this->modelIdColumnValue, $this);
        }

        Yii::$app->db
            ->createCommand()
            ->insert($this->tableName, [
                $this->modelClassColumn => get_class($this->owner),
                $this->modelIdColumn => $keyColumnValue,
                $this->dateTimeColumn => date('Y-m-d H:i:s'),
                $this->usernameColumn => $this->getUsername(),
                $this->logColumn => $this->textNewRecord,
            ])
            ->execute();
    }

    /**
     * Create a log entry when a model is updated.
     *
     * @param $event
     *
     * @throws \yii\db\Exception
     * @throws yii\base\InvalidConfigException
     */
    public function afterUpdate($event)
    {
        $log = [];

        # Text before the changed attributes text.
        if (!empty($this->textBeforeChanges)) {
            if (
                $this->textBeforeChanges instanceof \Closure ||
                is_array($this->textBeforeChanges)
            ) {
                $log[] = call_user_func($this->textBeforeChanges, $this);
            } else {
                $log[] = $this->textBeforeChanges;
            }
        }

        $hasChanges = false;
        foreach ($event->changedAttributes as $attr => $oldVal) {
            if (in_array($attr, $this->ignoredAttributes)) {
                continue;
            }

            $oldVal = (string) $oldVal;
            $newVal = (string) $event->sender->{$attr};
            if ($oldVal !== $newVal) {
                $hasChanges = true;

                list($oldVal, $newVal) = $this->getAttributeLogValues($attr, $oldVal, $newVal);

                $oldVal = static::checkEmpty($oldVal);
                $newVal = static::checkEmpty($newVal);

                $attrToken = Html::tag(
                    $this->attributeTokenTag,
                    $this->owner->getAttributeLabel($attr),
                    [
                        'class' => $this->attributeTokenCssClass,
                    ]
                );

                # Force div for html attributes for better presentation.
                $tag = $this->oldValueTokenTag;
                $cssClass = $this->oldValueTokenCssClass;
                if (in_array($attr, $this->htmlAttributes)) {
                    $tag = 'div';
                    $cssClass = 'div-changed-value';
                }

                $chgFrom = Html::tag($tag, trim($oldVal), ['class' => $cssClass]);

                # Force div for html attributes for better presentation.
                $tag = $this->newValueTokenTag;
                $cssClass = $this->newValueTokenCssClass;
                if (in_array($attr, $this->htmlAttributes)) {
                    $tag = 'div';
                    $cssClass = 'div-changed-value';
                }

                $chgTo = Html::tag($tag, trim($newVal), ['class' => $cssClass]);

                $log[] = "$attrToken {$this->textChangedFrom} $chgFrom {$this->textChangedTo} $chgTo";
            }
        }

        if (!$hasChanges) {
            return;
        }

        # Text after changed attributes description.
        if (!empty($this->textAfterChanges)) {
            if ($this->textAfterChanges instanceof \Closure || is_array($this->textAfterChanges)) {
                $log[] = call_user_func($this->textAfterChanges, $this);
            } else {
                $log[] = $this->textAfterChanges;
            }
        }

        $log = implode($this->glue, $log);

        # Value to be inserted in $keyColumn
        if ($this->modelIdColumnValue === null) {
            $keyColumnValue = $this->owner->getPrimaryKey();
        } else {
            $keyColumnValue = call_user_func($this->modelIdColumnValue, $this);
        }

        Yii::$app->db
            ->createCommand()
            ->insert($this->tableName, [
                $this->modelClassColumn => get_class($this->owner),
                $this->modelIdColumn => $keyColumnValue,
                $this->dateTimeColumn => date('Y-m-d H:i:s'),
                $this->usernameColumn => $this->getUsername(),
                $this->logColumn => $log,
            ])
            ->execute();
    }

    /**
     * Create a log entry when a model is deleted.
     *
     * @param $event
     *
     * @throws \yii\db\Exception
     */
    public function afterDelete($event)
    {
        $log = method_exists($this->owner, 'getDeletedRecordText')
            ? $this->owner->getDeletedRecordText()
            : $this->textDeletedRecord;

        Yii::$app->db
            ->createCommand()
            ->insert($this->tableName, [
                $this->modelClassColumn => get_class($this->owner),
                $this->modelIdColumn => $this->owner->getPrimaryKey(),
                $this->dateTimeColumn => date('Y-m-d H:i:s'),
                $this->usernameColumn => $this->getUsername(),
                $this->logColumn => $log,
            ])
            ->execute();
    }

    /**
     * Returns the old and new values of an attribute, considering the replacements that should be made.
     *
     * @param $attr the attribute to get the values from.
     * @param $oldVal the old value.
     * @param $newVal the new value.
     *
     * @return array
     */
    public function getAttributeLogValues($attr, $oldVal, $newVal)
    {
        # Values replacement.
        if (array_key_exists($attr, $this->valuesReplacement)) {
            # check if the value is a callback function
            if ($this->valuesReplacement[$attr] instanceof \Closure) {
                $resultado = call_user_func($this->valuesReplacement[$attr]);
                $oldVal = $resultado[$oldVal] ?? $oldVal;
                $newVal = $resultado[$newVal] ?? $newVal;
            } else {
                $oldVal = $this->valuesReplacement[$attr][$oldVal] ?? $oldVal;
                $newVal = $this->valuesReplacement[$attr][$newVal] ?? $newVal;
            }
        }

        # Date formatting.
        if (in_array($attr, $this->dateAttributes)) {
            if (false !== strpos($oldVal, '-')) {
                // mysql
                $oldVal = Yii::$app->formatter->asDate($oldVal);
            }
            if (false !== strpos($newVal, '-')) {
                // mysql
                $newVal = Yii::$app->formatter->asDate($newVal);
            }
        }

        # Date and time formatting.
        if (in_array($attr, $this->dateTimeAttributes)) {
            if (false !== strpos($oldVal, '-')) {
                // mysql
                $oldVal = Yii::$app->formatter->asDatetime($oldVal);
            }
            if (false !== strpos($newVal, '-')) {
                // mysql
                $newVal = Yii::$app->formatter->asDatetime($newVal);
            }
        }

        # Currency formatting.
        if (in_array($attr, $this->currencyAttributes)) {
            $oldVal = Yii::$app->formatter->asCurrency($oldVal);
            $newVal = Yii::$app->formatter->asCurrency($newVal);
        }

        if (in_array($attr, $this->htmlAttributes)) {
            $oldVal = strip_tags($oldVal, '<p>, <br>');
            $oldVal = str_replace(['<p>', '</p>'], ['', '<br>'], $oldVal);
            $newVal = strip_tags($newVal, '<p>, <br>');
            $newVal = str_replace(['<p>', '</p>'], ['', '<br>'], $newVal);
        }

        return [$oldVal, $newVal];
    }

    /**
     * Returs the username of the user that made the create/update operation.
     *
     * @return string
     */
    public function getUsername()
    {
        # check if this is a console app, which has no user.
        if (get_class(Yii::$app) !== 'yii\console\Application') {
            $user = Yii::$app->user->identity->username;
        } else {
            $user = 'Console'; // console app without a user
        }

        return $user;
    }

    /**
     * Checks if a value is empty.
     *
     * @param $val
     *
     * @return string
     */
    public static function checkEmpty($val)
    {
        if (empty($val)) {
            $val =
                '<span class="label label-default label-empty-value"><i>' .
                Yii::t('app', 'empty') .
                '</i></span>';
        }

        return $val;
    }
}
