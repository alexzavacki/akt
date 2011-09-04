<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_Glob_GlobIterator 
    extends Akt_Filesystem_Iterator_Glob_AbstractGlobIterator implements IteratorAggregate
{
    /**
     * Get iterator for internal glob pattern
     * 
     * @return Iterator|IteratorAggregate
     */
    public function getIterator()
    {
        return Akt_Filesystem_Path_Glob::needScan($this->_glob->getPattern())
            ? new Akt_Filesystem_Iterator_Glob_GlobScanIterator($this->_glob)
            : new Akt_Filesystem_Iterator_Glob_GlobStaticIterator($this->_glob);
    }
}