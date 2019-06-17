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

### GRID

#### SaveGridPaginationBehavior
Saves the grid's current page and pageSize in PHP Session so you can restore it later automatically when revisiting the page where the grid is.

Usage: On the model that will be used to generate the dataProvider that will populate the grid, attach this behavior.

```php
public function behaviors()
{
  return [
    'saveGridPage' => [
      'class' => SaveGridPaginationBehavior::class,
      'sessionVarName' => self::class . 'GridPage',
      'sessionPageSizeName' => self::class . 'GridPageSize'
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

#### SaveGridFiltersBehavior
Saves the Grid's current filters in PHP Session on every request and use [[loadWithFilters()]] to get the current filters and assign it to the grid.

Usage: On the model that will be used to generate the dataProvider that will populate the grid, attach this behavior.

```php
public function behaviors()
{
  return [
    'saveGridFilters' => [
      'class' => SaveGridFiltersBehavior::class,
      'sessionVarName' => self::class . 'GridFilters'
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

#### SaveGridOrderBehavior
Saves the Grid's current order criteria in PHP Session.

Usage: On the model that will be used to generate the dataProvider that will populate the grid, attach this behavior.

```php
public function behaviors()
{
    return [
        'saveGridOrder' => [
            'class' => SaveGridOrderBehavior::class,
            'sessionVarName' => self::class . 'GridOrder'
        ]
    ];
}
```

Then, on yout search() method, set the grid current order using these code:

```php
$dataProvider->sort->attributeOrders = $this->getGridOrder();
```



### ActiveRecord

#### LogChangesBehavior
Creates a log everytime a model is created or updated. The log entry contains all changed attributes, their old and new values.

Install: Create the necessary table by using the log_active_record.sql script or by copying the migration script to your migration directory and execute `yii migrate`.
If you want to use your own table, with your own naming conventions, you can customize the behaviour with your table name and columns. 

Usage: add it to the behaviors() method of your ActiveRecord model and customize it using it´s attributes.

```php
public function behaviors()
{
     return [
         'LogChanges' => [
             'class' => LogChangesBehavior::class,
             
             // Customization
             'valuesReplacement' => [
                 'active' => [
                     0 => 'No',
                     1 => 'Yes',
                 ]
             ],
             'currencyAttributes' => [
                 'subtotal', 'total', 'tax'
             ]
         ],
     ];
}
```

That's all!
