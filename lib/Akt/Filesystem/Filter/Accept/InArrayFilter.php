<?php

/**
 * 
 */
class Akt_Filesystem_Filter_Accept_InArrayFilter
    extends Akt_Filesystem_Filter_AbstractFilter
    implements Akt_Filesystem_Filter_Accept_AcceptFilter
{
    /**
     * Data array
     * @var array
     */
    protected $_array = array();
    
    
    /**
     * Constructor.
     * 
     * @param array $values
     */
    public function __construct($values = array())
    {
        if (!is_array($values)) {
            $values = array($values);
        }
        $this->_array = array_merge($this->_array, $values);
    }
    
    /**
     * Check if value exists in data array
     * 
     * @param  mixed $file
     * @return bool
     */
    public function accept($file)
    {
        return in_array($file, $this->_array);
    }
    
    /**
     * Add values to data array
     * 
     * @param  mixed $values
     * @return Akt_Filesystem_Filter_InArrayFilter
     */
    public function add($values)
    {
        if (!is_array($values)) {
            $values = array($values);
        }
        
        foreach ($values as $value) {
            if (!in_array($value, $this->_array)) {
                $this->_array[] = $value;
            }
        }
        
        return $this;
    }
}