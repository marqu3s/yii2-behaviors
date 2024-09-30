<?php

namespace marqu3s\behaviors;

use Yii;
use yii\data\ActiveDataProvider;
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
 *             'class' => SaveGridFiltersBehavior::class,
 *             'sessionVarName' => self::class . 'GridFilters'
 *         ]
 *     ];
 * }
 * ```
 *
 * Then, on your search() method, replace $this->load() by $dataProvider = $this->loadWithFilters($params, $dataProvider):
 *
 * ```
 * $dataProvider = new ActiveDataProvider(
 *     [
 *         'query' => $query,
 *         'sort' => ...,
 *     ]
 * );
 *
 * //$this->load($params); // <-- Replace or comment this
 * $dataProvider = $this->loadWithFilters($params, $dataProvider); // From SaveGridFiltersBehavior
 * ```
 *
 * You may combine this behavior with [[SaveGridPaginationBehavior]].
 *
 * That's all!
 *
 * @author Joao Marques <joao@jjmf.com>
 */
class SaveGridFiltersBehavior extends MarquesBehavior
{
    /** @var string the model class name without namespace. Used to detect filter values in $_GET['ModelClassName']. */
    public $modelShortClassName;

    /** @var bool control to check if filter values changed */
    private $filtersChanged = false;

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
            Yii::$app->session[$this->sessionVarName] = $this->owner->attributes;
            return;
        }

        $params =
            $this->requestMethod === 'GET'
                ? Yii::$app->request->queryParams
                : Yii::$app->request->post();

        if (isset($params[$this->modelShortClassName])) {
            # Check if the filter values have changed.
            $arr1 = $params[$this->modelShortClassName];
            $arr2 = Yii::$app->session[$this->sessionVarName];
            $this->filtersChanged = $arr1 != $arr2;
            Yii::$app->session[$this->sessionVarName] = $params[$this->modelShortClassName];
        }
    }

    /**
     * Load filters from $params or from session if no filters set in query string ($_GET).
     * If new filter values are detected in $params, the grid pagination is reset to page 1 (index 0).
     * Thats why we need the $dataProvider here.
     *
     * @param array $params
     * @param ActiveDataProvider $dataProvider
     *
     * @return ActiveDataProvider
     */
    public function loadWithFilters($params, $dataProvider)
    {
        $this->defineModelShortClassName();

        if (isset($params[$this->modelShortClassName])) {
            $this->owner->load($params);

            if ($this->filtersChanged) {
                $dataProvider->pagination->page = 0; // reset pagination to first page when applying new filters

                # Check if owner is using SaveGridPaginationBehavior.
                # If it is, reset the current page stored in session by SaveGridPaginationBehavior,
                $behaviors = $this->owner->getBehaviors();
                foreach ($behaviors as $behavior) {
                    if (get_class($behavior) == 'marqu3s\behaviors\SaveGridPaginationBehavior') {
                        $_SESSION[$behavior->sessionVarName] = $dataProvider->pagination->page;
                        $_GET[$behavior->getVarName] = $dataProvider->pagination->page;
                        break;
                    }
                }
            }
        } else {
            $this->owner->load([
                $this->modelShortClassName => Yii::$app->session[$this->sessionVarName],
            ]);
        }

        $dataProvider->refresh(); // refresh the data provider.

        return $dataProvider;
    }

    /**
     * Resets the filters stored in session.
     */
    public function resetGridFilters()
    {
        Yii::$app->session[$this->sessionVarName] = null;
        $this->filtersChanged = true;
    }
}
