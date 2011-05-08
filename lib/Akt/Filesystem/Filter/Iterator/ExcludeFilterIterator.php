<?php

/**
 * ExcludeFilterIterator
 */
class Akt_Filesystem_Filter_Iterator_ExcludeFilterIterator extends FilterIterator
{
    /**
     * Exclude filters
     * @var array 
     */
    protected $_exclude = array();


    /**
     * Constructor.
     * 
     * @param Iterator $iterator
     * @param array $exclude
     */
    public function __construct(Iterator $iterator, $exclude)
    {
        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }
        $this->_exclude = $exclude;
        
        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return Boolean true if the value should be kept, false otherwise
     */
    public function accept()
    {
        foreach ($this->_exclude as $exclude) 
        {
            if (!$exclude instanceof Akt_Filesystem_Filter_FilterInterface) {
                throw new Akt_Exception("Exclude filter must be"
                    . " instance of Akt_Filesystem_Filter_FilterInterface");
            }
            if ($exclude->accept($this->current())) {
                return false;
            }
        }
        
        return true;
    }
}