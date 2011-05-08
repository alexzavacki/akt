<?php

/**
 * FilesOnlyFilterIterator only keeps files.
 */
class Akt_Filesystem_Filter_Iterator_FilesOnlyFilterIterator extends FilterIterator
{
    /**
     * Filters the iterator value
     *
     * @return bool
     */
    public function accept()
    {
        return parent::current()->isFile();
    }
}