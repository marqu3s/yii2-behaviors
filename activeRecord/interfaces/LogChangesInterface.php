<?php

namespace marqu3s\behaviors\activeRecord\interfaces;

/**
 * Interface LogChangesInterface
 *
 * An ActiveRecord model that uses LogChangesBehavior may implement this interface to customize the text to be logged
 * when a record is deleted.
 */
interface LogChangesInterface
{
    /**
     * Returns the text to be logged when a record is deleted.
     *
     * @return string
     */
    public function getDeletedRecordText(): string;
}
