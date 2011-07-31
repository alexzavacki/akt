<?php

class Akt_Connection_Adapter_Ssh_Libssh2_Session implements Akt_Connection_Connection
{
    /**
     * Connection resource
     * @var resource
     */
    protected $_resource;
    
    /**
     * Connection authentication
     * @var Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication
     */
    protected $_authentication;
    
    /**
     * Session options
     * @var Akt_Options
     */
    protected $_options;
    
    
    /**
     * Constructor.
     * 
     * @param array|Akt_Options $options
     * @param Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication $authentication
     */
    public function __construct($options, $authentication = null)
    {
        $this->options()->set($options);
        
        if ($authentication !== null) {
            $this->setAuthentication($authentication);
        }
        
        if ($this->options()->get('auto_connect', false)) {
            $this->getResource();
        }
    }
    
    /**
     * Check if session resource is valid
     * 
     * @param  resource|null $resource
     * @return bool
     */
    public function isValid($resource = null)
    {
        if ($resource === null) {
            $resource = $this->_resource;
        } 
        return is_resource($resource);
    }
    
    /**
     * Get connection resource
     * 
     * @return resource
     */
    public function getResource()
    {
        if (!$this->isValid()) {
            $this->_createResource();
        }
        return $this->_resource;
    }
    
    /**
     * Create connection resource
     * 
     * @throws Akt_Exception
     * @return void
     */
    protected function _createResource()
    {
        $resource = $this->_connect();
        
        if (!$this->isValid($resource)) {
            throw new Akt_Exception("Libssh2 connection failed");
        }
        
        $this->_resource = $resource;
        
        if ($this->_authentication instanceof Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication) {
            $this->_authenticate();
        }
    }
    
    /**
     * Connect to server
     * 
     * @return resource
     */
    protected function _connect()
    {
        $options = $this->options();
        
        $hostname  = $options->get('hostname|host');
        $port      = $options->get('port', 22);
        $methods   = $options->get('methods', array());
        $callbacks = $options->get('callbacks', array());
        
        $socketTimeout = $options->get('socket_timeout');

        if ($socketTimeout) {
            $oldSocketTimeout = ini_get('default_socket_timeout');
            ini_set('default_socket_timeout', $socketTimeout);
        }
        
        $resource = ssh2_connect($hostname, $port, $methods, $callbacks);

        if (isset($oldSocketTimeout)) {
            ini_set('default_socket_timeout', $oldSocketTimeout);
        }
        
        return $resource;
    }
    
    /**
     * Authenticate connection
     * 
     * @throws Akt_Exception
     * @return void
     */
    protected function _authenticate()
    {
        if (!$this->_authentication instanceof Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication) {
            throw new Akt_Exception('Libssh2 authentication not set');
        }
        
        if (!$this->_authentication->authenticate($this->_resource)) {
            throw new Akt_Exception('Libssh2 authentication failed');
        }
    }
    
    /**
     * Set connection authentication
     * 
     * @param  Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication $authentication
     * @return Akt_Connection_Adapter_Ssh_Libssh2_Session
     */
    public function setAuthentication($authentication)
    {
        $this->_authentication = $authentication;
        return $this;
    }

    /**
     * Get options
     *
     * @return Akt_Options
     */
    public function options()
    {
        if (!$this->_options instanceof Akt_Options) {
            $this->_options = new Akt_Options();
        }
        return $this->_options;
    }
}
