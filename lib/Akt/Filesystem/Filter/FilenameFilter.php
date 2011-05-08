<?php

/**
 * 
 */
class Akt_Filesystem_Filter_FilenameFilter 
    implements Akt_Filesystem_Filter_FilterInterface
{
    /**
     * Filename adapter
     * @var Akt_Filesystem_Filter_FilterInterface 
     */
    protected $_adapter;
    
    
    /**
     * Constructor.
     *
     * @param string $adapter 
     * @param mixed  $arg1
     * @param mixed  ...
     */
    public function __construct($adapter)
    {
        $args = func_get_args();
        
        if (!isset($args[0])) {
            throw new Akt_Exception("No adapter specified");
        }
        
        $adapter = array_shift($args);
        
        $this->_adapter = $this->_createAdapter($adapter, $args);
    }
    
    /**
     * Create new adapter and pass arguments
     *
     * @param string $adapter
     * @param array $args 
     * @return Akt_Filesystem_Filter_FilterInterface
     */
    protected function _createAdapter($adapter, $args)
    {
        $className = $this->formatAdapterClassName($adapter);
        $r = new ReflectionClass($className);
        return $r->newInstanceArgs($args);
    }
    
    /**
     * Check if file should be kept
     *
     * @param SplFileInfo $fileinfo
     * @return bool 
     */
    public function accept($fileinfo)
    {
        if (!$this->_adapter instanceof Akt_Filesystem_Filter_FilterInterface) {
            throw new Akt_Exception("Adapter must be"
                . " instance of Akt_Filesystem_Filter_FilterInterface");
        }
        return $this->_adapter->accept($fileinfo);
    }
    
    /**
     * Get full class name for $adapter
     *
     * @param string $adapter
     * @return string
     */    
    public function formatAdapterClassName($adapter)
    {
        return 'Akt_Filesystem_Filter_Filename_' 
            . str_replace(' ', '_', ucwords(str_replace('_', ' ', $adapter)));
    }
    
    /**
     * Get current filename adapter
     *
     * @return Akt_Filesystem_Filter_FilterInterface 
     */
    public function getAdapter()     
    {
        return $this->_adapter;
    }
}