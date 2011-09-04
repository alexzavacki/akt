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
        if ($dir === null) {
            $this->_cwd = null;
            return true;
        }
        
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
     * @param  string $paths
     * @param  int $chmod
     * @param  bool $recursive
     * @return bool
     */
    public function mkdir($paths, $chmod = 0777, $recursive = true)
    {
        $result = true;
        $paths = $this->getFlattenPathsFromParam($paths, 'dir');
        
        foreach ($paths as $path) 
        {
            $path = $this->getAdapterStreamPath($path);
            
            if (!is_string($path)) {
                $result = false;
                continue;
            }
            if (is_dir($path)) {
                continue;
            }
            
            $result = mkdir($path, $chmod, $recursive) && $result;
        }
        
        return $result;
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
        $isTargetDir = $this->_isTargetValidDir($target, false);
        
        if (!is_string($source) && !$isTargetDir) {
            throw new Akt_Exception("To copy list of files target must be a directory");
        }
        
        $result = true;
        $source = $this->getFlattenPathsFromParam($source, 'file');
        
        foreach ($source as $key => $path)
        {
            if ($isTargetDir) {
                $targetFilename = rtrim($target, '\\/') . '/';
                $targetFilename .= Akt_Filesystem_Path::isAbsolute($path, 'any') 
                    ? basename($path)
                    : ltrim($path, '/\\');
            }
            else {
                $targetFilename = $target;
            }
            
            $sourceFilename = is_string($key) && Akt_Filesystem_Path::isAbsolute($key, 'any')
                ? $key
                : $path;
            
            $result = $this->_doCopy(
                $this->getAdapterStreamPath($sourceFilename),
                $targetFilename,
                $overwrite
            ) && $result;
        }

        return $result;
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
        $isTargetDir = $this->_isTargetValidDir($target);
        
        if (!is_string($source) && !$isTargetDir) {
            throw new Akt_Exception("To copy list of files target must be a directory");
        }
        
        if (is_string($source)) {
            $source = array($source);
        }
        elseif ($source instanceof Akt_Filesystem_List_AbstractList) {
            $source = $source->toArray();
        }
        elseif (!is_array($source)) {
            throw new Akt_Exception("Path must be a string, FileList or an array of paths");
        }
        
        $result = true;
        
        foreach ($source as $key => $path)
        {
            if ($isTargetDir) {
                $targetFilename = rtrim($target, '\\/') . '/';
                $targetFilename .= Akt_Filesystem_Path::isAbsolute($path, 'any') 
                    ? basename($path)
                    : ltrim($path, '/\\');
            }
            else {
                $targetFilename = $target;
            }
            
            $sourceFilename = is_string($key) && Akt_Filesystem_Path::isAbsolute($key, 'any')
                ? $key
                : $path;
            
            $result = $this->_doCopy(
                $sourceFilename,
                $this->getAdapterStreamPath($targetFilename),
                $overwrite
            ) && $result;
        }

        return $result;
    }

    /**
     * Copy a file
     * 
     * Can be used only with stream local files
     * For copying between different streams see download/upload
     * 
     * @param  string $source
     * @param  string $target
     * @param  bool $overwrite
     * @return bool
     */
    public function copy($source, $target, $overwrite = false)
    {
        $isTargetDir = $this->_isTargetValidDir($target);
        
        if (!is_string($source) && !$isTargetDir) {
            throw new Akt_Exception("To copy list of files target must be a directory");
        }
        
        $result = true;
        $source = $this->getFlattenPathsFromParam($source, 'file');
        
        foreach ($source as $key => $path)
        {
            if ($isTargetDir) {
                $targetFilename = rtrim($target, '\\/') . '/';
                $targetFilename .= Akt_Filesystem_Path::isAbsolute($path, 'any') 
                    ? basename($path)
                    : ltrim($path, '/\\');
            }
            else {
                $targetFilename = $target;
            }
            
            $sourceFilename = is_string($key) && Akt_Filesystem_Path::isAbsolute($key, 'any')
                ? $key
                : $path;
            
            $result = $this->_doCopy(
                $this->getAdapterStreamPath($sourceFilename),
                $this->getAdapterStreamPath($targetFilename),
                $overwrite
            ) && $result;
        }

        return $result;
    }
    
    /**
     * Check if passed target is dir and writable or string that ends with slash
     * 
     * @param  string $target
     * @param  bool $wrapAdapterStreamPath
     * @return bool
     */
    protected function _isTargetValidDir($target, $wrapAdapterStreamPath = true)
    {
        $isTargetDir = strtr(substr($target, -1), '\\', '/') == '/';
        
        if ($wrapAdapterStreamPath) {
            // getAdapterStreamPath() also removes ending slashes
            $target = $this->getAdapterStreamPath($target);
        }
        
        if (is_dir($target)) {
            return is_writable($target);
        }

        if ($isTargetDir && !is_file($target)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Copy source file to target
     * 
     * @param  string $sourceStreamPath
     * @param  string $targetStreamPath
     * @param  bool $overwrite
     * @return bool
     */
    protected function _doCopy($sourceStreamPath, $targetStreamPath, $overwrite = false)
    {
        if (!is_string($sourceStreamPath) || !is_file($sourceStreamPath)) {
            return false;
        }

        if (!is_string($targetStreamPath)) {
            return false;
        }
        
        if ($targetStreamPath == $sourceStreamPath) {
            return true;
        }

        if (!$overwrite && file_exists($targetStreamPath)) {
            return false;
        }
        
        $targetDirname = dirname($targetStreamPath);
        if (!is_dir($targetDirname)) {
            mkdir($targetDirname, 0777, true);
        }
        
        return copy($sourceStreamPath, $targetStreamPath);
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
        
        if (file_exists($target)) {
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
        
        if (!file_exists($path) || is_link($path)) {
            return false;
        }

        if (is_dir($path)) 
        {
            foreach (scandir($path) as $entry) {
                if ($entry != '.' && $entry != '..') {
                    $this->remove($path . '/' . $entry);
                }
            }
            rmdir($path);
        }
        elseif (is_file($path)) {
            return unlink($path);
        }
        
        return !file_exists($path);
    }
    
    /**
     * Get file size (in bytes)
     * 
     * @param  string $path
     * @return int|false
     */    
    public function filesize($path)
    {
        $path = $this->getAdapterStreamPath($path);
        
        if (!is_string($path) || !is_file($path)) {
            return false;
        }
        
        return sprintf("%u", filesize($path));
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
        
        if (!is_string($path) || !is_file($path) || !is_readable($path)) {
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
        if (!is_dir($dirname)) {
            mkdir($dirname, 0777, true);
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
    
    /**
     * Get flatten array of paths
     * 
     * @throws Akt_Exception
     * @param  string|array|Akt_Filesystem_List_AbstractList $paths
     * @param  string $type
     * @return array
     */
    public function getFlattenPathsFromParam($paths, $type = 'file')
    {
        if (is_string($paths)) {
            $paths = array($paths);
        }
        elseif ($paths instanceof Akt_Filesystem_List_AbstractList) {
            /** @var $paths Akt_Filesystem_List_AbstractList */
            $paths = $paths->withOptions(array(
                'cwd' => $this->getAdapterStreamPath('.')
            ))->toArray();
        }
        elseif (!is_array($paths)) {
            $listClass = $type == 'dir' ? 'DirList' : 'FileList';
            throw new Akt_Exception("Path must be a string, {$listClass} or an array of paths");
        }
        
        return $paths;
    }
}