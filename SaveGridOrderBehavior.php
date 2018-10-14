<?php

namespace marqu3s\behaviors;

use Yii;

/**
 * Saves the Grid's current order criteria in PHP Session.
 *
 * Usage: On the model that will be used to generate the dataProvider
 * that will populate the grid, attach this behavior.
 *
 * ```
 * public function behaviors()
 * {
 *     return [
 *         'saveGridOrder' =>[
 *             'class' => SaveGridOrderBehavior::className(),
 *             'sessionVarName' => self::className() . 'GridOrder'
 *         ]
 *     ];
 * }
 * ```
 * 
 * Then, on yout search() method, set the grid current order using these code:
 *
 * ```
 * $dataProvider->sort->attributeOrders = $this->getGridOrder();
 * ```
 *
 * @author Peppe Dantini
 */
class SaveGridOrderBehavior extends MarquesBehavior {

    /** @var string default $_GET parameter name */
    public $getVarName = 'sort';

    /**
     * @inheritdoc
     */
    public function events() {
        return [
            \yii\db\BaseActiveRecord::EVENT_INIT => [$this, 'saveGridOrder'],
        ];
    }

    /**
     * Saves the grid's current order criteria.
     */
    public function saveGridOrder() {
        if (!isset(Yii::$app->session[$this->sessionVarName])) {
            Yii::$app->session[$this->sessionVarName] = '';
        }

        if (Yii::$app->request->get($this->getVarName) !== null) {
            Yii::$app->session[$this->sessionVarName] = Yii::$app->request->get($this->getVarName);
        }
    }

    /**
     * Return the grid's current order criteria that was saved before.
     */
    public function getGridOrder() {
        $criteria = Yii::$app->session[$this->sessionVarName];
        return self::convertGridSort($criteria);
    }

    /**
     * The order criteria is managed as a string in the format used by $_GET: "field1,-field2";
     * so, before applying to the dataProvider, it must be converted in array format as required
     * by the "sort->attributeOrders" property.
     * @param string $criteria
     * @return array
     */    
    private static function convertGridSort($criteria) {
        $fields = explode(',', $criteria);
        $output = [];
        foreach ($fields as $field) {
            if (substr($field, 0, 1) == '-') {
                $field = substr($field, 1);
                $order = SORT_DESC;
            } else {
                $order = SORT_ASC;
            }
            $output[$field] = $order;
        }
        return $output;
    }
    
}
