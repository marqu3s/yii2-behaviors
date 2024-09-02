<?php

namespace marqu3s\behaviors;

use yii\base\Behavior;
use yii\base\InvalidConfigException;

/**
 * Class MarquesBehavior
 * Base class for the behaviors.
 */
class MarquesBehavior extends Behavior
{
    /** @var string default session variable name */
    public $sessionVarName = '';

    public $requestMethod = 'GET';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->sessionVarName)) {
            throw new InvalidConfigException(
                'The $sessionVarName should be configured for this behavior.'
            );
        }
    }
}
