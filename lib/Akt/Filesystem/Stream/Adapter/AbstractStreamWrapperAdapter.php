<?php

abstract class Akt_Filesystem_Stream_Adapter_AbstractStreamWrapperAdapter
    implements Akt_Filesystem_Stream_Adapter_StreamAdapter
{
    /**
     * Current working dir
     * @var string
     */
    protected $_cwd;
    

    /**
     * Get current working dir
     * 
     * @return string
     */
    public function getcwd()
    {
        return $this->_cwd;
    }
    
    /**
     * Check whether a file or directory exists
     * 
     * @param  string $path
     * @return bool
     */
    public function fileExists($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? file_exists($path) : false;                    
    }

    /**
     * Check if $path is dir and exists
     * 
     * @param  string $path
     * @return bool
     */
    public function isDir($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? is_dir($path) : false;            
    }
    
    /**
     * Check if $path is file and exists
     * 
     * @param  string $path
     * @return bool
     */
    public function isFile($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? is_file($path) : false;                
    }
    
    /**
     * Check if $path exists and is a symbolic link
     * 
     * @param  string $path
     * @return bool
     */
    public function isLink($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? is_link($path) : false;
    }
    
    /**
     * Check whether a $path exists and is readable
     * 
     * @param  string $path
     * @return bool
     */
    public function isReadable($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? is_readable($path) : false;               
    }
    
    /**
     * Check whether a $path exists and is writable
     * 
     * @param  string $path
     * @return bool
     */    
    public function isWritable($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? is_writable($path) : false;             
    }
         
    /**
     * Change current working dir
     * 
     * @param  string $dir
     * @return bool
     */
    public function chdir($dir)
    {
        $dir = $this->getStreamPath($dir);

        if (!is_string($dir) || !$this->isDir($dir)) {
            return false;
        }
        
        $this->_cwd = rtrim($dir, '\\/');
        return true;
    }

    /**
     * Create a directory (recursively by default)
     * 
     * @param  string $path
     * @param  int $chmod
     * @param  bool $recursive
     * @return bool
     */
    public function mkdir($path, $chmod = 0777, $recursive = true)
    {
        $path = $this->getAdapterStreamPath($path);
        
        if (!is_string($path)) {
            return false;
        }
        if ($this->isDir($path)) {
            return true;
        }
        
        return mkdir($path, $chmod, $recursive);
    }
    
    /**
     * Remove an empty directory
     * 
     * @param  string $path
     * @return bool
     */
    public function rmdir($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? rmdir($path) : false;
    }
    
    /**
     * Open a directory and get it's handle resource
     * 
     * @param  string $path
     * @return resource|bool
     */
    public function opendir($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? opendir($path) : false;
    }
    
    /**
     * Download a file from the stream
     * 
     * @param  string $source
     * @param  string $target
     * @param  bool $overwrite
     * @return bool
     */
    public function download($source, $target, $overwrite = false)
    {
        $source = $this->getAdapterStreamPath($source);
        
        if (!is_string($source) || !$this->isFile($source) || !$this->isReadable($source)) {
            return false;
        }
        
        if (!is_string($target)) {
            return false;
        }
        
        $isTargetDir = strtr(substr($target, -1), '\\', '/') == '/';
        if ($isTargetDir) {
            $target = rtrim($target, '\\/') . '/' . basename($source);
        }
        
        if ($this->getAdapterStreamPath($target) == $source) {
            return false;
        }

        if (!$overwrite && file_exists($target)) {
            return false;
        }
        
        $targetDirname = dirname($target);
        if (!is_dir($targetDirname)) {
            mkdir($targetDirname, 0777, true);
        }
        
        return copy($source, $target);
        
        /*
        $targetHandle = fopen($target, 'wb');
        if (!$targetHandle) {
            return false;
        }
        
        $sourceHandle = $this->fopen($source, 'rb');
        if (!$sourceHandle) {
            fclose($targetHandle);
            return false;
        }
        
        $filesize = (int) $this->filesize($source);
        $bytesRead = 0;
        
        while (!feof($sourceHandle) && $bytesRead < $filesize) {
            $chunk = fread($sourceHandle, 8192);
            $bytesRead += strlen($chunk);
            fwrite($targetHandle, $chunk);
        }
        
        fclose($targetHandle);
        fclose($sourceHandle);
        
        return true;
        */
    }

    /**
     * Upload a file to the stream
     * 
     * @param  string $source
     * @param  string $target
     * @param  bool $overwrite
     * @return bool
     */
    public function upload($source, $target, $overwrite = false)
    {
        if (!is_string($source)) {
            return false;
        }
        
        if (!is_string($target)) {
            return false;
        }
        
        $isTargetDir = strtr(substr($target, -1), '\\', '/') == '/';
        if ($isTargetDir) {
            $target = rtrim($target, '\\/') . '/' . basename($source);
        }
        
        $target = $this->getAdapterStreamPath($target);
        
        if (!is_string($target)) {
            return false;
        }
        
        if ($target == $this->getAdapterStreamPath($source)) {
            return false;
        }

        if (!$overwrite && $this->fileExists($target)) {
            return false;
        }
        
        $targetDirname = dirname($target);
        if (!$this->isDir($targetDirname)) {
            $this->mkdir($targetDirname);
        }
        
        return copy($source, $target);
    }

    /**
     * Copy a file
     * 
     * MUST be used only with stream local files
     * For copying between different streams see download/upload
     * 
     * @param  string $source
     * @param  string $target
     * @param  bool $overwrite
     * @return bool
     */
    public function copy($source, $target, $overwrite = false)
    {
        $source = $this->getAdapterStreamPath($source);
        
        if (!is_string($source) || !$this->isFile($source)) {
            return false;
        }

        $isTargetDir = strtr(substr($target, -1), '\\', '/') == '/';
        if ($isTargetDir) {
            $target = rtrim($target, '\\/') . '/' . basename($source);
        }
        
        $target = $this->getAdapterStreamPath($target);
        
        if (!is_string($target)) {
            return false;
        }
        
        if ($target == $source) {
            return false;
        }
        
        if (!$overwrite && $this->fileExists($target)) {
            return false;
        }
        
        $targetDirname = dirname($target);
        if (!$this->isDir($targetDirname)) {
            $this->mkdir($targetDirname);
        }
        
        return copy($source, $target);
    }
        
    /**
     * Rename a file or directory
     * 
     * @param  string $origin
     * @param  string $target
     * @return bool
     */
    public function rename($origin, $target)
    {
        $origin = $this->getAdapterStreamPath($origin);
        $target = $this->getAdapterStreamPath($target);
        
        if (!is_string($origin) || !is_string($target)) {
            return false;
        }
        
        if ($this->fileExists($target)) {
            return false;
        }
        
        return rename($origin, $target);
    }
    
    /**
     * Move a file or directory
     * 
     * Alias of self::rename()
     * 
     * @param  string $origin
     * @param  string $target
     * @return bool
     */
    public function move($origin, $target)
    {
        return $this->rename($origin, $target);
    }
    
    /**
     * Delete a file
     * 
     * @param  string $path
     * @return bool
     */
    public function unlink($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? unlink($path) : false;
    }
    
    /**
     * Remove a file or directory recursively
     * 
     * @param  string $path
     * @return bool
     */
    public function remove($path)
    {
        $path = $this->getAdapterStreamPath($path);
        
        if (!is_string($path)) {
            return false;
        }
        
        if (!$this->fileExists($path) || $this->isLink($path)) {
            return false;
        }

        if ($this->isDir($path)) 
        {
            foreach (scandir($path) as $entry) {
                if ($entry != '.' && $entry != '..') {
                    $this->remove($path . '/' . $entry);
                }
            }
            $this->rmdir($path);
        }
        elseif ($this->isFile($path)) {
            return $this->unlink($path);
        }
        
        return !$this->fileExists($path);
    }
    
    /**
     * Get file size (in bytes)
     * 
     * @param  string $path
     * @return int|false
     */    
    public function filesize($path)
    {
        if (!$this->isFile($path)) {
            return false;
        }
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? sprintf("%u", filesize($path)) : false;        
    }
        
    /**
     * Open a file and get it's handle resource
     * 
     * @param  string $path
     * @param  string $mode
     * @return resource|bool
     */
    public function fopen($path, $mode)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? fopen($path, $mode) : false;
    }
    
    /**
     * Get file content
     * 
     * @param  string $path
     * @param  int|null $maxlen
     * @param  int $offset
     * @return string|bool
     */
    public function readFile($path, $maxlen = null, $offset = -1)
    {
        $path = $this->getAdapterStreamPath($path);
        
        if (!is_string($path) || !$this->isFile($path) || !$this->isReadable($path)) {
            return false;
        }
        
        // Unexpected results when passing null value as $maxlen param
        return ($maxlen !== null)
            ? file_get_contents($path, false, null, $offset, $maxlen)
            : file_get_contents($path, false, null, $offset);
    }
    
    /**
     * Write data to the file
     * 
     * @param  string $path
     * @param  string $data
     * @param  string $mode
     * @return int|bool
     */
    public function writeFile($path, $data, $mode = 'w')
    {
        $path = $this->getAdapterStreamPath($path);
        
        if (!is_string($path)) {
            return false;
        }
        
        $dirname = dirname($path);
        
        if (!$this->isDir($dirname)) {
            $this->mkdir($dirname);
        }

        if (($handle = fopen($path, $mode)) === false) {
            return false;
        }
        $result = fwrite($handle, $data);
        fclose($handle);
        
        return $result;
    }
    
    /**
     * Append data to the file
     * 
     * @param  string $path
     * @param  string $data
     * @return int|bool
     */
    public function appendFile($path, $data)
    {
        return $this->writeFile($path, $data, 'a');
    }

    /**
     * Get file last access time (unix timestamp)
     * 
     * @param  string $path
     * @return int|bool
     */
    public function lastAccess($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? fileatime($path) : false;
    }
    
    /**
     * Get file last modified time (unix timestamp)
     * 
     * @param  string $path
     * @return int|bool
     */
    public function lastModified($path)
    {
        $path = $this->getAdapterStreamPath($path);
        return is_string($path) ? filemtime($path) : false;
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
        throw new Akt_Exception("Method " . get_class($this) . "::{$method}() not found");
    }
    
    /**
     * Check whether $path is valid for current stream adapter
     * 
     * @param  string $path
     * @return bool
     */
    public function isValidStreamPath($path)
    {
        if (Akt_Filesystem_Path::isUnc($path)) {
            return $this->isValidStreamUncPath($path);
        }
        if (Akt_Filesystem_Path::isStreamWrapped($path)) {
            return $this->isValidStreamWrapperPath($path);
        }
        if (Akt_Filesystem_Path::isAbsoluteWin($path)) {
            return $this->isValidStreamAbsoluteWinPath($path);
        }
        if (Akt_Filesystem_Path::isAbsoluteUnix($path)) {
            return $this->isValidStreamAbsoluteUnixPath($path);
        }
        return $this->isValidStreamOtherPath($path);
    }

    /**
     * Check that UNC path is valid for current stream adapter
     * @param  string $path
     * @return bool
     */
    public function isValidStreamUncPath($path)
    {
        return false;
    }

    /**
     * Check that stream wrapped path is valid for current stream adapter
     * @param  string $path
     * @return bool
     */
    public function isValidStreamWrapperPath($path)
    {
        return false;
    }

    /**
     * Check that absolute win path is valid for current stream adapter
     * @param  string $path
     * @return bool
     */
    public function isValidStreamAbsoluteWinPath($path)
    {
        return false;
    }

    /**
     * Check that absolute unix path is valid for current stream adapter
     * @param  string $path
     * @return bool
     */
    public function isValidStreamAbsoluteUnixPath($path)
    {
        return true;
    }
    
    /**
     * Check that other path is valid for current stream adapter
     * @todo: make characters validation for all isValid* functions
     * @param  string $path
     * @return bool
     */
    public function isValidStreamOtherPath($path)
    {
        return true;
    }
    
    /**
     * Get absolute realized stream path
     * 
     * @param  string $path
     * @return string|false
     */
    public function getStreamPath($path)
    {
        if (!is_string($path) || trim($path) == '') {
            return false;
        }
        
        if (!$this->isValidStreamPath($path)) {
            return false;
        }
        
        if ($this->isAdapterStreamPath($path)) {
            $path = $this->removeAdapterPartFromPath($path);
        }
        elseif (!Akt_Filesystem_Path::isAbsoluteLocal($path, true) && is_string($this->_cwd)) {
            $path = $this->_cwd . '/' . $path;
        }
        
        if (Akt_Filesystem_Path::isAbsoluteWin($path) 
            && !$this->isValidStreamAbsoluteWinPath($path)
        ) {
            return false;
        }
        
        return Akt_Filesystem_Path::realize($path, '/');        
    }
    
    /**
     * Get formatted adapter stream path
     * 
     * @param  string $path
     * @return string|false
     */
    public function getAdapterStreamPath($path)
    {
        $path = $this->getStreamPath($path);
        return is_string($path) ? $this->wrapAdapterStreamPath($path) : false;
    }
    
    /**
     * Wrap stream path with adapter specific syntax
     * @abstract
     * @param  string $path
     * @return string
     */
    abstract public function wrapAdapterStreamPath($path);

    /**
     * Check that $path is correctly formatted adapter stream path
     * 
     * @param  string $path
     * @return bool
     */
    public function isAdapterStreamPath($path)
    {
        return Akt_Filesystem_Path::isStreamWrapped($path) 
            && $this->isValidStreamWrapperPath($path);
    }
    
    /**
     * Remove adapter specific part and get clean local stream path
     * 
     * @param  string $path
     * @return string
     */
    public function removeAdapterPartFromPath($path)
    {
        return $path;
    }
}