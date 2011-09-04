<?php

/**
 * 
 */
abstract class Akt_Filesystem_Iterator_Glob_AbstractGlobIterator
{
    /**
     * Glob filter
     * @var Akt_Filesystem_Filter_Accept_Pathname_Glob
     */
    protected $_glob;
    
    
    /**
     * Constructor.
     * 
     * @throws Akt_Exception
     * @param  string|Akt_Filesystem_Filter_Accept_Pathname_Glob $glob
     */
    public function __construct($glob)
    {
        if (is_string($glob)) {
            $glob = new Akt_Filesystem_Filter_Accept_Pathname_Glob($glob);
        }
        elseif (!$glob instanceof Akt_Filesystem_Filter_Accept_Pathname_Glob) {
            throw new Akt_Exception("Glob filter must be a string or an instance of Akt_Filesystem_Filter_Accept_Pathname_Glob");
        }
        
        $this->_glob = $glob;
    }
}