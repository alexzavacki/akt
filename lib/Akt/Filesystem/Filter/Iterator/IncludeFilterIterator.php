<?php

/**
 * IncludeFilterIterator
 */
class Akt_Filesystem_Filter_Iterator_IncludeFilterIterator extends FilterIterator
{
    /**
     * Include filters
     * @var array 
     */
    protected $_include = array();


    /**
     * Constructor.
     * 
     * @param Iterator $iterator
     * @param array $include
     */
    public function __construct(Iterator $iterator, $include)
    {
        if (!is_array($include)) {
            $include = array($include);
        }
        $this->_include = $include;
        
        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return Boolean true if the value should be kept, false otherwise
     */
    public function accept()
    {
        foreach ($this->_include as $include) 
        {
            if (!$include instanceof Akt_Filesystem_Filter_FilterInterface) {
                throw new Akt_Exception("Include filter must be"
                    . " instance of Akt_Filesystem_Filter_FilterInterface");
            }
            if ($include->accept($this->current())) {
                return true;
            }
        }
        
        return false;
    }
}