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
 * Then, on yout search() method, set the grid current order using one of these:
 *
 * ```
 * $dataProvider->sort->attributeOrders = GenLib::convertGridSort($this->getGridOrder());
 * ```
 *
 * Il criterio di ordinamento viene gestito come stringa nella forma utilizzata
 * dal GET: "campo1,-campo2". La stringa restituita va poi convertita in array
 * (MiaLib.converteGridSort) per poter essere inserita in "sort->attributeOrders".
 *
 * @author Peppe Dantini
 */
class SaveGridOrderBehavior extends MarquesBehavior {

    /** @var string Nome del parametro utilizzato in $_GET */
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
     * Salva il criterio di ordinamento corrente
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
     * Restituisce il criterio di ordinamento salvato precedentemente
     */
    public function getGridOrder() {
        return Yii::$app->session[$this->sessionVarName];
    }

}
