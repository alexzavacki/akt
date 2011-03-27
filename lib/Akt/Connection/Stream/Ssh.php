<?php

class Akt_Connection_Stream_Ssh extends Akt_Connection_Stream_AbstractStream
{
    /**
     * Sftp connection handle
     * @var resource
     */
    protected $_sftp;


    /**
     * Connect
     *
     * @return void
     */
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

        $this->_sftp = ssh2_sftp($this->_connectionHandle);
    }

    /**
     * Get stream wrapped path
     *
     * @param string $path
     * @return string
     */
    public function getStreamWrappedPath($path)
    {
        $path = '/' . ltrim($path, '/\\');
        return "ssh2.sftp://{$this->_sftp}{$path}";
    }

    /**
     * Get all methods supported by this adapter
     *
     * @return array
     */
    public function getAdapterMethods()
    {
        return array_keys(array(
            'fileExists' => 'file_exists',
            'file_exists' => 'file_exists',
        ));
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
        /*
        if ($this->isMethodCallable($method)) {
            $function = $this->getStreamFunctionFromMethod($method);
            return $function($this->getStreamWrappedPath($path));
        }
        */

        throw new Akt_Exception("Method ::{$method}() not found");
    }

    /**
     * Check if file exists
     *
     * @param string $path
     * @return bool
     */
    public function fileExists($path)
    {
        return file_exists($this->getStreamWrappedPath($path));
    }
}