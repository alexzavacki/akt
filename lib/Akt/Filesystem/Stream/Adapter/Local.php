<?php

class Akt_Filesystem_Stream_Adapter_Local
    extends Akt_Filesystem_Stream_Adapter_AbstractStreamWrapperAdapter
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->chdir(getcwd());
    }
    
    /**
     * Get stream wrapper's URL-path prefix
     * 
     * @return string
     */
    public function getStreamWrapperPathPrefix()
    {
        return 'file://';
    }

    /**
     * Check that stream wrapped path is valid for current stream adapter
     * @param  string $path
     * @return bool
     */
    public function isValidStreamWrapperPath($path)
    {
        return strpos($path, $this->getStreamWrapperPathPrefix()) === 0;
    }

    /**
     * Check that absolute win path is valid for current stream adapter
     * @param  string $path
     * @return bool
     */
    public function isValidStreamAbsoluteWinPath($path)
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Get absolute realized stream path
     * 
     * @param  string $path
     * @return string
     */
    public function getStreamPath($path)
    {
        $path = parent::getStreamPath($path);
        if ($path === false) {
            return false;
        }
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $path = Akt_Filesystem_Path::getAbsoluteWinPath($path);
        }
        $realpath = realpath($path);
        return is_string($realpath) ? $realpath : Akt_Filesystem_Path::clean($path);
    }
    
    /**
     * Wrap stream path with adapter specific syntax
     * 
     * @param  string $path
     * @return string
     */
    public function wrapAdapterStreamPath($path)
    {
        return $path;
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
        $leftTrimmedPath = ltrim($path, '\\/');
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if (Akt_Filesystem_Path::isAbsoluteWin($leftTrimmedPath)) {
                $path = $leftTrimmedPath;
            }
            else {
                $path = Akt_Filesystem_Path::getAbsoluteWinPath($path);
            }
        }
        return $path;
    }
}