<?php

class Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Publickey 
    implements Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication
{
    /**
     * Auth username
     * @var string
     */
    protected $_username;
    
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
     * Constructor.
     * 
     * @param string $username
     * @param string $publicKeyFile
     * @param string $privateKeyFile
     * @param string|null $passPhrase
     */
    public function __construct($username, $publicKeyFile, $privateKeyFile, $passPhrase = null)
    {
        $this->_username       = (string) $username;
        $this->_publicKeyFile  = (string) $publicKeyFile;
        $this->_privateKeyFile = (string) $privateKeyFile;
        $this->_passPhrase     = $passPhrase;
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
        return ssh2_auth_pubkey_file(
            $session,
            $this->_username, 
            $this->_publicKeyFile, 
            $this->_privateKeyFile,
            $this->_passPhrase
        );
    }
}