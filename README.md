# Yii2 Behaviors

## Installation

The preferred way to install this extension is through [composer](http://composer.org). Either run:

```
php composer.phar require --prefer-dist marqu3s/yii2-behaviors "*"
```

or add

```
"marqu3s/yii2-behaviors": "*"
```

to the require section of your composer.json file.

## Available Behaviors

### SaveGridPaginationBehavior
Saves the grid's current page and pageSize in PHP Session so you can restore it later automatically when revisiting the page where the grid is.

Usage: On the model that will be used to generate the dataProvider that will populate the grid, attach this behavior.

```php
public function behaviors()
{
  return [
    'saveGridPage' =>[
      'class' => SaveGridPaginationBehavior::className(),
      'sessionVarName' => self::className() . 'GridPage',
      'sessionPageSizeName' => self::className() . 'GridPageSize'
    ]
  ];
}
```

Then, on your search() method, set the grid current page using one of these:

```php
$dataProvider = new ActiveDataProvider(
  [
    'query' => $query,
    'sort' => ...,
    'pagination' => [
      'page' => $this->getGridPage(), // <- Prefered method
      'pageSize' => $this->getGridPageSize(),
      ...
    ]
  ]
);
```

OR

```php 
$dataProvider->pagination->page = $this->getGridPage();
```

### SaveGridFiltersBehavior
Saves the Grid's current filters in PHP Session on every request and use [[loadWithFilters()]] to get the current filters and assign it to the grid.

Usage: On the model that will be used to generate the dataProvider that will populate the grid, attach this behavior.

```php
public function behaviors()
{
  return [
    'saveGridFilters' =>[
      'class' => SaveGridFiltersBehavior::className(),
      'sessionVarName' => self::className() . 'GridFilters'
    ]
  ];
}
```

Then, on your search() method, replace $this->load() by $dataProvider = $this->loadWithFilters($params, $dataProvider):

```php
$dataProvider = new ActiveDataProvider(
  [
    'query' => $query,
    'sort' => ...,
    'pagination' => [
      'page' => $this->getGridPage(), // <- Prefered method
      'pageSize' => $this->getGridPageSize(),
      ...
    ]
  ]
);

//$this->load($params); // <-- Replace or comment this
$dataProvider = $this->loadWithFilters($params, $dataProvider); // From SaveGridFiltersBehavior
```

### SaveGridOrderBehavior
Saves the Grid's current order criteria in PHP Session.

Usage: On the model that will be used to generate the dataProvider that will populate the grid, attach this behavior.

```php
public function behaviors()
{
    return [
        'saveGridOrder' =>[
            'class' => SaveGridOrderBehavior::className(),
            'sessionVarName' => self::className() . 'GridOrder'
        ]
    ];
}
```

Then, on yout search() method, set the grid current order using these code:

```php
$dataProvider->sort->attributeOrders = GenLib::convertGridSort($this->getGridOrder());
```

The order criteria is managed as a string in the format used by $_GET: "field1,-field2"; so, before applying to the dataProvider, you must convert in array format as required by the "sort->attributeOrders" property. This is the function needed for this:

```php
   public static function convertGridSort($criteria) {
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
```

That's all!
