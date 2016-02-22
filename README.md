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
Saves the grid's current page in PHP Session so you can restore it later automatically when revisiting the page where the grid is.

Usage: On the model that will be used to generate the dataProvider that will populate the grid, attach this behavior.

```php
public function behaviors()
{
  return [
    'saveGridPage' =>[
      'class' => SaveGridPaginationBehavior::className(),
      'sessionVarName' => self::className() . 'GridPage'
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
      ...
    ]
  ]
);

//$this->load($params); // <-- Replace or comment this
$dataProvider = $this->loadWithFilters($params, $dataProvider); // From SaveGridFiltersBehavior
```

That's all!
