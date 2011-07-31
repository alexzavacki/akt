<?php

class Akt_Connection_Adapter_Ssh_Libssh2_Authentication_None 
    implements Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication
{
    /**
     * Auth username
     * @var string
     */
    protected $_username;
    
    
    /**
     * Constructor.
     * 
     * @param string $username
     */
    public function __construct($username)
    {
        $this->_username = (string) $username;
    }
    
    /**
     * Authenticate session
     *
     * @param  Akt_Connection_Adapter_Ssh_Libssh2_Session|resource $session
     * @return bool
     */
    function authenticate($session)
    {
        if ($session instanceof Akt_Connection_Adapter_Ssh_Libssh2_Session) {
            $session = $session->getResource();
        }
        return ssh2_auth_none($session, $this->_username) === true;
    }
}