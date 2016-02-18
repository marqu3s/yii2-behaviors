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
Saves the Grid's current page in PHP Session so you can restore it later automatically when revisiting the page where the grid is.

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

Then, on yout search() method, set the grid current page using one of these:

```php
$dataProvider = new ActiveDataProvider(
  [
    'query' => $query,
    'sort' => ...,
    'pagination' => [
      'page' => $this->getGridPage(),
      ...
    ]
  ]
);
```

OR

```php 
$dataProvider->pagination->page = $this->getGridPage();
```

That's all!

