<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_Glob_GlobScanIterator 
    extends Akt_Filesystem_Iterator_Glob_AbstractGlobIterator implements IteratorAggregate
{
    /**
     * @return Iterator
     */
    public function getIterator()
    {
        $flags = Akt_Filesystem_Iterator_RecursiveDirectoryIterator::SKIP_DOTS;

        $iterator = new RecursiveIteratorIterator(
            new Akt_Filesystem_Iterator_Glob_GlobRecursiveDirectoryIterator($this->_glob, $flags),
            RecursiveIteratorIterator::SELF_FIRST
        );

        return $iterator;
    }
}