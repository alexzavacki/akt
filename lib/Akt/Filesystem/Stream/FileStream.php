<?php

class Akt_Filesystem_Stream_FileStream
{
    /**
     * Stream adapter
     * If null then local adapter will be used
     * @var Akt_Filesystem_Stream_Adapter_StreamAdapter
     */
    protected $_adapter;
    
    /**
     * Default adapter class name
     * @var string
     */
    protected $_defaultAdapter = 'Akt_Filesystem_Stream_Adapter_Local';
    
    
    /**
     * Constructor.
     * 
     * @param Akt_Filesystem_Stream_Adapter_StreamAdapter|Akt_Connection_Connection $adapter
     */
    public function __construct($adapter = null)
    {
        if ($adapter instanceof Akt_Connection_Connection) {
            $adapter = Akt_Connection_StreamAdapter_Factory::create($adapter);
        }
        elseif ($adapter === null) {
            $adapter = $this->_createDefaultAdapter();
        }
        
        if (!$adapter instanceof Akt_Filesystem_Stream_Adapter_StreamAdapter) {
            throw new Akt_Exception("Adapter must implements StreamAdapter interface");
        }
        
        $this->setAdapter($adapter);
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
        if ($this->_adapter instanceof Akt_Filesystem_Stream_Adapter_StreamAdapter) {
            return call_user_func_array(array($this->_adapter, $method), $args);
        }
        throw new Akt_Exception("Method " . __CLASS__ . "::{$method}() not found");
    }
    
    /**
     * Get stream adapter
     * 
     * @return Akt_Filesystem_Stream_Adapter_StreamAdapter
     */
    public function getAdapter()
    {
        if (!$this->_adapter instanceof Akt_Filesystem_Stream_Adapter_StreamAdapter) {
            $this->_adapter = $this->_createDefaultAdapter();
        }
        return $this->_adapter;
    }
    
    /**
     * Set stream adapter
     * 
     * @param  Akt_Filesystem_Stream_Adapter_StreamAdapter $adapter
     * @return Akt_Filesystem_Stream_FileStream
     */
    public function setAdapter(Akt_Filesystem_Stream_Adapter_StreamAdapter $adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }
    
    /**
     * Create and return default adapter
     * 
     * @return Akt_Filesystem_Stream_Adapter_StreamAdapter
     */
    protected function _createDefaultAdapter()
    {
        return new $this->_defaultAdapter();
    }
}
