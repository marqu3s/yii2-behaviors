<?php

namespace marqu3s\behaviors;

use Yii;

/**
 * Salva l'ordinamento corrente della grid nelle variabili di sessione.
 * Basato sull'estensione "yii2-behaviors" di Joao Marques <joao@jjmf.com>
 * https://www.yiiframework.com/extension/yii2-behaviors
 * https://github.com/marqu3s/yii2-behaviors
 * 
 * Il criterio di ordinamento viene gestito come stringa nella forma utilizzata
 * dal GET: "campo1,-campo2". La stringa restituita va poi convertita in array
 * (MiaLib.converteGridSort) per poter essere inserita in "sort->attributeOrders".
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
