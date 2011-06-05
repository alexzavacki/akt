<?php

/**
 * 
 */
class Akt_Helper_Array
{
    /**
     * Check if array contains at least one not numeric key
     *
     * @param array $array
     * @return bool
     */
    public static function hasStringKey($array)
    {
        foreach ($array as $key => $value) {
            if (!ctype_digit((string) $key)) {
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
        
        // iterator_to_array() doesn't work properly for recursive iterators
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        foreach ($it as $value) {
            $flatted[] = $value;
        }
        
        return $unique ? array_unique($flatted) : $flatted;
    }
}
