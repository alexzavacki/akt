<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_FormatIterator extends IteratorIterator
{
    /**
     * Registered format filters
     * @var array
     */
    protected $_formatters = array();
    
    
    /**
     * Constructor.
     * 
     * @param Traversable $iterator
     * @param array|Akt_Filesystem_Filter_Format_FormatFilter $formatters
     */
    public function __construct(Traversable $iterator, $formatters = array())
    {
        if ($formatters) {
            $this->add($formatters);
        }
        parent::__construct($iterator);
    }
    
    /**
     * Wrapper for parent's current() and apply format filters for its value
     * 
     * @return mixed
     */
    public function current()
    {
        $file = $this->getInnerIterator()->current();

        foreach ($this->_formatters as $formatter) {
            /** @var $formatter Akt_Filesystem_Filter_Format_FormatFilter */
            $file = $formatter->format($file);
        }

        return $file;
    }
    
    /**
     * Add format filters
     * 
     * @param  array|Akt_Filesystem_Filter_Format_FormatFilter $formatters
     * @return Akt_Filesystem_Iterator_FormatIterator
     */
    public function add($formatters)
    {
        if (!is_array($formatters)) {
            $formatters = array($formatters);
        }
        
        foreach ($formatters as $formatter) {
            if (!$formatter instanceof Akt_Filesystem_Filter_Format_FormatFilter) {
                throw new Akt_Exception("Format filter must implement Akt_Filesystem_Filter_FormatFilter");
            }
            $this->_formatters[] = $formatter;
        }
        
        return $this;
    }
}
