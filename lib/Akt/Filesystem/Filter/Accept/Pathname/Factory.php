<?php

/**
 * 
 */
class Akt_Filesystem_Filter_Accept_Pathname_Factory
{
    /**
     * Singleton
     */
    protected function __construct() {}
    protected function __clone() {}

    
    /**
     * Create new filename filter adapter and pass arguments
     *
     * @param  string $adapter
     * @param  array $args 
     * @return Akt_Filesystem_Filter_Accept_Pathname_AbstractPathnameFilter
     */
    public static function create($adapter, $args)
    {
        $className = self::formatAdapterClassName($adapter);
        $r = new ReflectionClass($className);
        return $r->newInstanceArgs($args);
    }

    /**
     * Get full class name for $adapter
     *
     * @param string $adapter
     * @return string
     */    
    public static function formatAdapterClassName($adapter)
    {
        return 'Akt_Filesystem_Filter_Accept_Pathname_' 
            . str_replace(' ', '_', ucwords(str_replace('_', ' ', $adapter)));
    }
}