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
            if (!is_numeric($key)) {
                return true;
            }
        }
        return false;
    }
}