<?php

/**
 * Aggregate? Append? Or something else?
 */
class Akt_Filesystem_Filter_FilterAggregate
    implements Akt_Filesystem_Filter_FilterInterface
{
    /**
     * Filters array
     * @var array 
     */
    protected $_filters = array();
    
    
    /**
     * Check if file should be kept
     *
     * @param SplFileInfo $fileinfo
     * @return bool 
     */
    public function accept($fileinfo)
    {
        foreach ($this->_filters as $filter) {
            if (!$filter->accept($fileinfo)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Calling of nonexistent method
     *
     * @param  string $method
     * @param  array  $args
     * @return Akt_Filesystem_Filter_FilterAggregate
     */
    public function __call($method, $args)
    {
        $this->_defaultFilter($method, $args);
        return $this;
    }
    
    /**
     * Used when calling unknown method of the class
     *
     * @param string $filter
     * @param array $args 
     * @return void
     */
    protected function _defaultFilter($filter, $args)
    {
        throw new Akt_Exception("No default filter set for this filter aggregator");
    }
    
    /**
     * Get all filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * Add custom filter
     *
     * @param array $filters
     * @return Akt_Filesystem_Filter_FilterAggregate 
     */
    public function appendFilter($filters)
    {
        if (!is_array($filters)) {
            $filters = array($filters);
        }
        
        foreach ($filters as $filter) {
            $this->_filters[] = $filter;
        }
        
        return $this;
    }

    /**
     * Set filters
     *
     * @param array $filters
     * @return Akt_Filesystem_Filter_FilterAggregate 
     */
    public function setFilters($filters)
    {
        $this->_filters = array();
        $this->appendFilter($filters);
        return $this;
    }
}