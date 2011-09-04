<?php

/**
 * ExcludeFilterIterator
 */
class Akt_Filesystem_Iterator_Filter_ExcludeFilterIterator extends FilterIterator
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
        $this->_exclude = array_merge($this->_exclude, $exclude);
        
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
            $file = $this->getInnerIterator()->current();
            
            if ($exclude instanceof Akt_Filesystem_Filter_Accept_AcceptFilter) {
                /** @var $exclude Akt_Filesystem_Filter_Accept_AcceptFilter */
                if ($exclude->accept($file)) {
                    return false;
                }
            }
            elseif (is_callable($exclude)) {
                /** @var $exclude callback */
                if (!call_user_func($exclude, $file)) {
                    return false;
                }
            }
            else {
                throw new Akt_Exception("Invalid exclude filter");
            }
        }
        
        return true;
    }
}