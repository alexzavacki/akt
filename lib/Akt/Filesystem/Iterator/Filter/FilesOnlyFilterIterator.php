<?php

/**
 * FilesOnlyFilterIterator only keeps files.
 */
class Akt_Filesystem_Iterator_Filter_FilesOnlyFilterIterator extends FilterIterator
{
    /**
     * Filters the iterator value
     *
     * @return bool
     */
    public function accept()
    {
        return is_file($this->getInnerIterator()->getPathname());
    }
}