<?php

namespace marqu3s\behaviors;

use Yii;

/**
 * Saves the Grid's current page in PHP Session on a new page request
 * and use [[getPage()]] to get the current page and assign it
 * to the Pagination configuration.
 *
 * Saves also the pageSize.
 *
 * Usage: On the model that will be used to generate the dataProvider
 * that will populate the grid, attach this behavior.
 *
 * NOTE: If using together with [[SaveGridFiltersBehavior]], make sure to load this
 * behavior before [[SaveGridFiltersBehavior]].
 *
 * ```
 * public function behaviors()
 * {
 *     return [
 *         // load saveGridPage before saveGridFilters.
 *         'saveGridPage' => [
 *             'class' => SaveGridPaginationBehavior::className(),
 *             'sessionVarName' => self::className() . 'GridPage'
 *             'sessionPageSizeName' => self::className() . 'GridPageSize'
 *         ],
 *         'saveGridFilters' => [
 *             'class' => SaveGridFiltersBehavior::class,
 *             'sessionVarName' => self::class . 'GridFilters',
 *         ],
 *     ];
 * }
 * ```
 *
 * Then, on your search() method, set the grid current page like this
 * after applying all possible filters:
 *
 * ```
 * $dataProvider = new ActiveDataProvider(
 *     [
 *         'query' => $query,
 *         'sort' => ...,
 *     ]
 * );
 *
 * $dataProvider = $this->loadWithFilters($params, $dataProvider); // From SaveGridFiltersBehavior
 *
 * ... apply all other filters here ...
 *
 * // Configure pagination settings here. This will ensure an accurate total count.
 * // DO NOT configure these when setting the new ActiveDataProvider
 * // as it will count the total number of itens before the filters are applied.
 * $dataProvider->pagination->totalCount = (clone $query)->count();
 * $dataProvider->pagination->pageSize = 50;
 * $dataProvider->pagination->pageParam = $this->gridPageVarName;
 * $dataProvider->pagination->page = $this->getGridPage();
 *
 * return $dataProvider;
 * ```
 *
 * That's all!
 *
 * @author Joao Marques <joao@jjmf.com>
 */
class SaveGridPaginationBehavior extends MarquesBehavior
{
    /** @var string default $_GET parameter name for the page to be loaded. */
    public $getVarName = 'page';

    /** @var string default $_GET parameter name for the items per page to be shown. */
    public $getPageSizeName = 'per-page';

    /** @var string default $_SESSION variable name to store the quantity of items per page. */
    public $sessionPageSizeName = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        # Set a default sessionPageSizeName value.
        if (empty($this->sessionPageSizeName)) {
            $this->sessionPageSizeName = $this->sessionVarName . '-per-page';
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            \yii\db\BaseActiveRecord::EVENT_INIT => [$this, 'saveGridPage'],
        ];
    }

    /**
     * Saves the grid's current active page index.
     */
    public function saveGridPage()
    {
        if (!isset(Yii::$app->session[$this->sessionVarName])) {
            Yii::$app->session[$this->sessionVarName] = 0;
        }
        if (!isset(Yii::$app->session[$this->sessionPageSizeName])) {
            Yii::$app->session[$this->sessionPageSizeName] = 10;
        }

        if (Yii::$app->request->get($this->getVarName) !== null) {
            Yii::$app->session[$this->sessionVarName] =
                (int) Yii::$app->request->get($this->getVarName) - 1;
        }
        if (Yii::$app->request->get($this->getPageSizeName) !== null) {
            Yii::$app->session[$this->sessionPageSizeName] = (int) Yii::$app->request->get(
                $this->getPageSizeName
            );
        }
    }

    /**
     * Return the grid's current active page index that was saved before or the page being requested.
     */
    public function getGridPage()
    {
        // $page = Yii::$app->request->get($this->getVarName);
        // if ($page !== null) {
        //     $page = (int) $page - 1;
        //     Yii::$app->session[$this->sessionVarName] = $page;
        //     return $page;
        // }

        return Yii::$app->session[$this->sessionVarName];
    }

    /**
     * Return the grid's page size that was saved before.
     */
    public function getGridPageSize()
    {
        return Yii::$app->session[$this->sessionPageSizeName];
    }
}
