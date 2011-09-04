<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_Filter_CallbackFilterIterator extends FilterIterator
{
    /**
     * Custom filter callbacks array
     * @var array
     */
    protected $_callbacks = array();
    

    /**
     * Constructor.
     *
     * @param Iterator $iterator
     * @param array $callbacks
     */
    public function __construct(Iterator $iterator, $callbacks = array())
    {
        if ($callbacks) {
            $this->add($callbacks);
        }
        parent::__construct($iterator);
    }
    
    /**
     * Filters the iterator value
     *
     * @return bool
     */
    public function accept()
    {
        $file = $this->getInnerIterator()->current();

        foreach ($this->_callbacks as $callback) {
            if (!call_user_func($callback, $file)) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * Add values to data array
     * 
     * @param  mixed $callbacks
     * @return Akt_Filesystem_Iterator_Filter_CallbackFilterIterator
     */
    public function add($callbacks)
    {
        if (!is_array($callbacks) || is_callable($callbacks)) {
            $callbacks = array($callbacks);
        }
        
        foreach ($callbacks as $callback) {
            if (!is_callable($callback)) {
                throw new Akt_Exception('Invalid callback');
            }            
            $this->_callbacks[] = $callback;
        }
        
        return $this;
    }
}