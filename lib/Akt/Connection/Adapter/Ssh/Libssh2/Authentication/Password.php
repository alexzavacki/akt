<?php

class Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Password 
    implements Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication
{
    /**
     * Auth username
     * @var string
     */
    protected $_username;
    
    /**
     * Auth password
     * @var string
     */
    protected $_password;
    
    
    /**
     * Constructor.
     * 
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->_username = (string) $username;
        $this->_password = (string) $password;
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
        return ssh2_auth_password($session, $this->_username, $this->_password);
    }
}
