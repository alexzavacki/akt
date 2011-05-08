<?php

/**
 * DirectoriesOnlyFilterIterator only keeps directories.
 */
class Akt_Filesystem_Filter_Iterator_DirectoriesOnlyFilterIterator extends FilterIterator
{
    /**
     * Filters the iterator value
     *
     * @return bool
     */
    public function accept()
    {
        return parent::current()->isDir();
    }
}