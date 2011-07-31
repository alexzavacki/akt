<?php

class Akt_Connection_Adapter_Ssh_Libssh2_Sftp implements Akt_Connection_Connection
{
    /**
     * Connection session
     * @var Akt_Connection_Ssh_Libssh2_Session
     */
    protected $_session;
    
    /**
     * Sftp resource
     * @var resource
     */
    protected $_resource;
    

    /**
     * Constructor.
     * 
     * @param Akt_Connection_Adapter_Ssh_Libssh2_Session $session
     */
    public function __construct($session)
    {
        $this->setSession($session);
    }
    
    /**
     * Check if sftp resource is valid
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
     * Get sftp resource
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
     * Create sftp resource
     * 
     * @throws Akt_Exception
     * @return void
     */
    protected function _createResource()
    {
        $resource = ssh2_sftp($this->getSessionResource());

        if (!is_resource($resource)) {
            throw new Akt_Exception('Sftp initialization failed');
        }

        $this->_resource = $resource;
    }
    
    /**
     * Get sftp's session resource
     * 
     * @throws Akt_Exception
     * @return resource
     */
    public function getSessionResource()
    {
        if (!$this->_session instanceof Akt_Connection_Adapter_Ssh_Libssh2_Session) {
            throw new Akt_Exception('Sftp session not set');
        }
        return $this->_session->getResource();
    }
    
    /**
     * Set sftp session
     * 
     * @param  $session
     * @return Akt_Connection_Adapter_Ssh_Libssh2_Sftp
     */
    public function setSession($session)
    {
        if ($this->_session !== $session 
            && ($session instanceof Akt_Connection_Adapter_Ssh_Libssh2_Session)
        ) {
            $this->_session = $session;
            $this->_resource = null;
        }
        return $this;
    }
    
    /**
     * Get sftp session
     * 
     * @return Akt_Connection_Adapter_Ssh_Libssh2_Session
     */
    public function getSession()
    {
        return $this->_session;
    }
}