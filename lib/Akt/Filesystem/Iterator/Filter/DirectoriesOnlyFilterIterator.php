<?php

/**
 * DirectoriesOnlyFilterIterator only keeps directories.
 */
class Akt_Filesystem_Iterator_Filter_DirectoriesOnlyFilterIterator extends FilterIterator
{
    /**
     * Filters the iterator value
     *
     * @return bool
     */
    public function accept()
    {
        return is_dir($this->getInnerIterator()->getPathname());
    }
}