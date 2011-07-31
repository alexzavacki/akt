<?php

class Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Hostbased 
    implements Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication
{
    /**
     * Auth username
     * @var string
     */
    protected $_username;
    
    /**
     * Hostname
     * @var string
     */
    protected $_hostname;
    
    /**
     * Path to public key file
     * @var string
     */
    protected $_publicKeyFile;
    
    /**
     * Path to private key file
     * @var string
     */
    protected $_privateKeyFile;
    
    /**
     * User pass phrase
     * @var string
     */
    protected $_passPhrase;
    
    /**
     * Local username
     * @var string
     */
    protected $_localUsername;
    
    
    /**
     * Constructor.
     * 
     * @param string $username
     * @param string $hostname
     * @param string $publicKeyFile
     * @param string $privateKeyFile
     * @param string|null $passPhrase
     * @param string|null $localUsername If omitted, then the value for username will be used for it. 
     */
    public function __construct($username, $hostname, $publicKeyFile, $privateKeyFile, 
        $passPhrase = null, $localUsername = null
    ) {
        $this->_username       = (string) $username;
        $this->_hostname       = (string) $hostname;
        $this->_publicKeyFile  = (string) $publicKeyFile;
        $this->_privateKeyFile = (string) $privateKeyFile;
        $this->_passPhrase     = $passPhrase;
        $this->_localUsername  = $localUsername;
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
        return ssh2_auth_hostbased_file(
            $session,
            $this->_username, 
            $this->_hostname, 
            $this->_publicKeyFile, 
            $this->_privateKeyFile,
            $this->_passPhrase,
            $this->_localUsername
        );
    }
}