<?php

/**
 * 
 */
class Akt_Filesystem_Sync_FileSync
{
    /**
     * Sync adapter instance
     * @var Akt_Filesystem_Sync_Adapter_SyncAdapter
     */
    protected $_adapter;
    
    
    /**
     * Constructor.
     * 
     * @param Akt_Filesystem_Sync_Adapter_SyncAdapter $adapter
     * @param array $params
     */
    public function __construct($adapter, $params = array())
    {
        if (!$adapter instanceof Akt_Filesystem_Sync_Adapter_SyncAdapter) {
            throw new Akt_Exception('Adapter must be instance of Akt_Filesystem_Sync_Adapter_SyncAdapter');
        }
        
        $this->_adapter = $adapter;
        
        if (is_array($params) && $params) {
            $this->_adapter->setOptions($params);
        }
    }
    
    /**
     * Create and return new sync
     * 
     * @static
     * @param  Akt_Filesystem_Sync_Adapter_SyncAdapter $adapter
     * @param  array $params
     * @return Akt_Filesystem_Sync_FileSync
     */
    public static function create($adapter, $params = array())
    {
        return new self($adapter, $params);
    }
    
    /**
     * Calling of nonexistent method
     *
     * @param  string $method
     * @param  array  $args
     * @return void
     */
    public function __call($method, $args)
    {
        if ($this->_adapter instanceof Akt_Filesystem_Sync_Adapter_SyncAdapter) {
            return call_user_func_array(array($this->_adapter, $method), $args);
        }
        throw new Akt_Exception("Method " . __CLASS__ . "::{$method}() not found");
    }
}