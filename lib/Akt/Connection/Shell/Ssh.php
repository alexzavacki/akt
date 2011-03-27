<?php

class Akt_Connection_Shell_Ssh extends Akt_Connection_Shell_AbstractShell
{
    public function connect()
    {
        $hostname = $this->getOption('host|hostname');
        $port = $this->getOption('port', 22);
        $username = $this->getOption('username|user|login');
        $password = $this->getOption('password|pass|pwd|passwd');
        $socketTimeout = intval($this->getOption('socket_timeout'));

        if ($socketTimeout) {
            $oldSocketTimeout = ini_get('default_socket_timeout');
            ini_set('default_socket_timeout', $socketTimeout);
        }

        $this->_connectionHandle = @ssh2_connect($hostname, $port);

        if ($socketTimeout) {
            ini_set('default_socket_timeout', $oldSocketTimeout);
        }

        if (!is_resource($this->_connectionHandle)) {
            throw new Akt_Exception("Unable to connect to {$hostname}:{$port}");
        }

        if (!@ssh2_auth_password($this->_connectionHandle, $username, $password)) {
            throw new Akt_Exception("Authentication failed");
        }
    }
}