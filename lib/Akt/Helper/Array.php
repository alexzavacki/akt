<?php

/**
 * 
 */
class Akt_Helper_Array
{
    /**
     * Check if array contains at least one value with non-numeric key
     *
     * @param array $array
     * @return bool
     */
    public static function hasStringKey($array)
    {
        foreach ($array as $key => $value) {
            if (intval($key) != $key) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Flatten multi-dimensional array
     * 
     * @param array $array
     * @return array
     */
    public static function flatten($array, $unique = false)
    {
        $flatted = array();
        
        // iterator_to_array() doesn't work properly with recursive iterators
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        foreach ($it as $value) {
            $flatted[] = $value;
        }
        
        return $unique ? array_unique($flatted) : $flatted;
    }
}