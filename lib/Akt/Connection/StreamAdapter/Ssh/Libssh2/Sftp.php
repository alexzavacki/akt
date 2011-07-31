<?php

class Akt_Connection_StreamAdapter_Ssh_Libssh2_Sftp
    extends Akt_Connection_StreamAdapter_AbstractConnectionStreamWrapperAdapter
{
    /**
     * Get stream wrapper's URL-path prefix
     * 
     * @return string
     */
    public function getStreamWrapperPathPrefix()
    {
        return sprintf(
            'ssh2.sftp://%s', 
            $this->getConnection()->getResource()
        );        
    }

    /**
     * Check that stream wrapped path is valid for current stream adapter
     * 
     * @param  string $path
     * @return bool
     */
    public function isValidStreamWrapperPath($path)
    {
        return strpos($path, $this->getStreamWrapperPathPrefix()) === 0;
    }

    /**
     * Wrap stream path with adapter specific syntax
     * 
     * @param  string $path
     * @return string
     */
    public function wrapAdapterStreamPath($path)
    {
        return $this->getStreamWrapperPathPrefix() . '/' . ltrim($path, '\\/');
    }
    
    /**
     * Remove adapter specific part and get clean local stream path
     * 
     * @param  string $path
     * @return string
     */
    public function removeAdapterPartFromPath($path)
    {
        if ($this->isValidStreamWrapperPath($path)) {
            $path = substr($path, strlen($this->getStreamWrapperPathPrefix()));
        }
        return '/' . ltrim($path, '\\/');
    }

    /**
     * Check whether a file or directory exists
     * 
     * It seems like libssh2 extension doesn't clear stat cache after unlink,
     * so we need more strict file existence checking
     * 
     * @param  string $path
     * @return bool
     */
    public function fileExists($path)
    {
        $path = $this->getAdapterStreamPath($path);
        
        if (!is_string($path)) {
            return false;
        }
        
        if (!file_exists($path)) {
            // File doesn't exist, so we don't need other checkings
            return false;
        }
        
        if ($this->isFile($path)) {
            // Try to open file for reading
            if (($exists = @fopen($path, 'r')) === false) {
                return false;
            }
            @fclose($exists);
        }

        return true;
    }
}