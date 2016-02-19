<?php

namespace marqu3s\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;

/**
 * Saves the Grid's current filters in PHP Session on every request
 * and use [[loadWithFilters()]] to get the current filters and assign it
 * to the grid.
 *
 * Usage: On the model that will be used to generate the dataProvider
 * that will populate the grid, attach this behavior.
 *
 * ```
 * public function behaviors()
 * {
 *     return [
 *         'saveGridFilters' =>[
 *             'class' => SaveGridFiltersBehavior::className(),
 *             'sessionVarName' => self::className() . 'GridFilters'
 *         ]
 *     ];
 * }
 * ```
 *
 * Then, on yout search() method, replace $this->load() by $this->loadWithFilters():
 *
 * ```
 * $dataProvider = new ActiveDataProvider(
 *     [
 *         'query' => $query,
 *         'sort' => ...,
 *         'pagination' => [
 *             'page' => $this->getGridPage(), // From SaveGridPaginationBehavior
 *             ...
 *         ]
 *     ]
 * );
 * //$this->load($params); // <-- Replace or comment this
 * $this->loadWithFilters($params); // From SaveGridFiltersBehavior
 * ```
 *
 * That's all!
 *
 * @author Joao Marques <joao@jjmf.com>
 */
class SaveGridFiltersBehavior extends Behavior
{
    /** @var string the model class name without namespace. Used to detect filter values in $_GET['ModelClassName']. */
    public $modelShortClassName;

    /** @var string default session variable name */
    public $sessionVarName = 'gridFilter';

    /**
     * Define the short class name of the model in use.
     */
    public function defineModelShortClassName()
    {
        $reflect = new \ReflectionClass($this->owner);
        $this->modelShortClassName = $reflect->getShortName(); // Class name without namespace
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_INIT => [$this, 'saveGridFilters'],
        ];
    }

    /**
     * Saves the grid's current filters.
     */
    public function saveGridFilters()
    {
        $this->defineModelShortClassName();

        if (!isset(Yii::$app->session[$this->sessionVarName])) {
            Yii::$app->session[$this->sessionVarName] = [];
        }

        $params = Yii::$app->request->queryParams;
        if (isset($params[$this->modelShortClassName])) {
            Yii::$app->session[$this->sessionVarName] = $params[$this->modelShortClassName];
        }
    }

    /**
     * Load filters from $params or from session if no filters set in query string ($_GET).
     * If new filter values are detected in $params, the grid pagination is reset to page 1 (index 0).
     * Thats why we need the $dataProvider here.
     *
     * @param $params array
     * @param $dataProvider \yii\data\ActiveDataProvider
     * @return \yii\data\ActiveDataProvider
     */
    public function loadWithFilters($params, $dataProvider)
    {
        $this->defineModelShortClassName();

        if (isset($params[$this->modelShortClassName])) {
            $this->owner->load($params);
            $dataProvider->pagination->page = 0; // reset pagination to first page when applying new filters
        } else {
            $this->owner->load([$this->modelShortClassName => Yii::$app->session[$this->sessionVarName]]);
        }

        return $dataProvider;
    }
}
