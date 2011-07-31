<?php

class Akt_Connection_StreamAdapter_Factory 
{
    /**
     * Create and return stream adapter by connection type
     * 
     * @static
     * @param  Akt_Connection_Connection $connection
     * @return Akt_Connection_Adapter_ConnectionStreamAdapter
     */
    public static function create($connection)
    {
        $adapterName = self::getAdapterClassFromConnection($connection);
        return new $adapterName($connection);
    }
    
    /**
     * Create stream adapter by name
     * 
     * @static
     * @param  string $adapterName
     * @return Akt_Connection_Adapter_ConnectionStreamAdapter
     */
    public static function createAdapter($adapterName)
    {
        $adapterName = self::formatAdapterName($adapterName);
        return new $adapterName();
    }
    
    /**
     * Get formatted adapter class name
     * 
     * @static
     * @param  string $name
     * @return string
     */
    public static function formatAdapterName($name)
    {
        $prefix = 'Akt_Connection_StreamAdapter_';
        
        if (strpos(strtolower($name), strtolower($prefix)) !== 0) {
            $name = $prefix . $name;
        }
        
        return implode('_', array_map('ucfirst', explode('_', $name)));
    }
    
    /**
     * Get adapter class name for specified connection object or name
     * 
     * @static
     * @throws Akt_Exception
     * @param  Akt_Connection_Connection|string $connection
     * @return string
     */
    public static function getAdapterClassFromConnection($connection)
    {
        if (is_object($connection)) {
            $connection = get_class($connection);
        }
        
        $prefix = 'Akt_Connection_Adapter_';
        foreach (Akt_Helper_Class::getParents($connection, true) as $connectionClass) {
            if (strpos(strtolower($connectionClass), strtolower($prefix)) === 0
                && Akt_Helper_Class::isInstantiatable($connectionClass)
            ) {
                return self::formatAdapterName(trim(substr($connectionClass, strlen($prefix)), '_'));
            }
        }
        
        throw new Akt_Exception("Adapter class for connection '{$connection}' not found");
    }
}