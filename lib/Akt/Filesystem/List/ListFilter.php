<?php

/**
 * 
 */
class Akt_Filesystem_List_ListFilter 
    extends Akt_Filesystem_Filter_FilterAggregate
{
    /**
     * Used when calling unknown method of the class
     * 
     * Add filename filter
     *
     * @param string $filter 
     * @param array  $args
     * @return Akt_Filesystem_List_ListFilter
     */
    protected function _defaultFilter($filter, $args)
    {
        $args = array_merge(array($filter), (array) $args);
        
        $r = new ReflectionClass('Akt_Filesystem_Filter_FilenameFilter');
        
        $instance = $r->newInstanceArgs($args);
        $this->appendFilter($instance);
        
        return $this;
    }    
}