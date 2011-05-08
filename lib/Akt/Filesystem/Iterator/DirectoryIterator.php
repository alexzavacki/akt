<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_DirectoryIterator implements Iterator
{
    /**
     * @const
     */
    const FOLLOW_SYMLINKS = 512;
    const SKIP_DOTS       = 4096;
    const UNIX_PATHS      = 8192;
    
    /**
     * Base dir path
     * @var string
     */
    protected $_path;

    /**
     * Iterator flags
     * @var int
     */
    protected $_flags = 0;
    
    /**
     * Opened dir resource
     * @var resource
     */
    protected $_resource;
    
    /**
     * Current file name
     * @var string 
     */
    protected $_currentFile;
    
    /**
     * Current position of internal pointer
     * @var int  
     */
    protected $_position;
    
    /**
     * Use dir reopening instead of rewinddir
     * @var bool
     */
    protected $_useReopen = null;
    
    
    /**
     * Constructor.
     * 
     * @param string $path
     * @param int $flags
     */
    public function __construct($path, $flags = 0)
    {
        if (is_numeric($flags)) {
            $this->_flags = (int) $flags;
        }
        
        $dirsep = $this->hasFlag(self::UNIX_PATHS) 
            ? Akt_Filesystem_Path::DIRSEP_UNIX
            : null;
        
        $this->_path = rtrim(Akt_Filesystem_Path::clean($path, $dirsep), "/\\");
    }

    /**
     * Destructor.
     * 
     * @return void
     */
    public function __destruct()
    {
        $this->_close();
    }

    /**
     * Open dir and get resource
     * 
     * If no path specified or null, iterator's internal path will be used
     *
     * @param string|null $path
     * @return resource 
     */
    protected function _open($path = null)
    {
        if ($path === null) {
            $path = $this->_path;
        }
        
        if (!is_string($path)) {
            throw new Akt_Exception("Path must be a string");
        }
        
        $path = rtrim($path, "/\\") . $this->getDirectorySeparator($path);
        
        if (!is_dir($path)) {
            throw new Akt_Exception("Path '{$path}' is not directory");
        }
        
        $this->_resource = opendir($path);
        
        if (!is_resource($this->_resource)) {
            throw new Akt_Exception("Path '{$path}' can't be read");
        }

        return $this->_resource;
    }

    /**
     * Close dir and remove resource
     * 
     * @return void
     */
    protected function _close()
    {
        if (is_resource($this->_resource)) {
            closedir($this->_resource);
        }
        $this->_resource = null;
    }

    /**
     * Reopen current dir
     *
     * @return Akt_Filesystem_Iterator_DirectoryIterator 
     */
    protected function _reopen()
    {
        $this->_close();
        $this->_open();
        return $this;
    }

    /**
     * Rewind opened dir
     * 
     * @return void
     */
    protected function _rewind()
    {
        rewinddir($this->getResource());
    }

    /**
     * Rewind the Iterator to the first element
     * 
     * @return void
     */
    public function rewind()
    {
        // rewinddir (seeking) doesn't work for some stream wrappers (e.g. ssh2.sftp)
        if (($this->_useReopen === null 
                && Akt_Filesystem_Path::isStreamWrapped($this->_path)) 
            || $this->_useReopen) 
        {
            $this->_reopen();
        }
        else {
            $this->_rewind();
        }
        
        $this->_currentFile = null;
        $this->_position = null;
        
        $this->_getNextFile();
    }

    /**
     * Move forward to next element
     * 
     * @return void
     */
    public function next()
    {
        $this->_getNextFile();
    }

    /**
     * Checks if current position is valid
     * 
     * @return bool
     */
    public function valid()
    {
        return $this->getFile() !== false;
    }

    /**
     * Return the current element
     * 
     * Return information about current file as instance of SplFileInfo
     *
     * @return SplFileInfo
     */
    public function current()
    {
        $path = $this->getPath() . $this->getDirectorySeparator() . $this->getFile();
        return new SplFileInfo($path);
    }
    
    /**
     * Return the key of the current element
     * 
     * Get current file position
     *
     * @return int 
     */
    public function key()
    {
        return $this->_position;
    }
    
    /**
     * Get or create dir resource
     *
     * @return resource|null
     */
    public function getResource()
    {
        if (is_resource($this->_resource)) {
            return $this->_resource;
        }
        return $this->_open();
    }
    
    /**
     * Get current file name
     *
     * @return string 
     */
    public function getFile()
    {
        if ($this->_currentFile !== null) {
            return $this->_currentFile;
        }
        return $this->_getNextFile();
    }
    
    /**
     * Get next file name in opened dir
     *
     * @return string 
     */
    protected function _getNextFile()
    {
        $resource = $this->getResource();
        $skipDots = $this->hasFlag(self::SKIP_DOTS);
        
        do {
            $this->_currentFile = readdir($resource);
        } 
        while ($skipDots && $this->isDot());
        
        $this->_position = $this->_position !== null ? $this->_position + 1 : 0;
        
        return $this->_currentFile;
    }
    
    /**
     * Check if file is '.' or '..'
     * 
     * @return bool 
     */
    public function isDot($file = null)
    {
        if ($file === null) {
            $file = $this->getFile();
        }
        return $file == '.' || $file == '..';
    }
    
    /**
     * Get current directory separator
     * 
     * Returns '/' if current path is stream wrapped
     * Returns '\' if OS is Windows, path is local and no UNIX_PATHS in current flags
     * In all other cases returns '/'
     *
     * @return string 
     */
    public function getDirectorySeparator($path = null)
    {
        if ($path === null) {
            $path = $this->_path;
        }
        if (Akt_Filesystem_Path::isStreamWrapped($path)) {
            return '/';
        }
        if (Akt_Filesystem_Path::isUnc($path)) {
            return $this->hasFlag(self::UNIX_PATHS) ? '/' : '\\';
        }
        if (strstr(PHP_OS, 'WIN') && !$this->hasFlag(self::UNIX_PATHS)) {
            return "\\";
        }
        return '/';
    }
    
    /**
     * Short alias of getDirectorySeparator()
     * 
     * Get current directory separator

     * @return string 
     */
    public function dirSeparator($path = null)
    {
        return $this->getDirectorySeparator($path);
    }
    
    /**
     * Get current dir path
     *
     * @return string 
     */
    public function getPath()     
    {
        return $this->_path;
    }

    /**
     * Get current iterator flags
     *
     * @return int 
     */
    public function getFlags()
    {
        return $this->_flags;
    }

    /**
     * Set new iterator flags
     *
     * @param int $flags 
     * @return Akt_Filesystem_Iterator_DirectoryIterator
     */
    public function setFlags($flags)
    {
        $this->_flags = (int) $flags;
        return $this;
    }
    
    /**
     * Check if iterator has specified flag
     *
     * @param int $flag
     * @return bool 
     */
    public function hasFlag($flag)
    {
        return ($this->_flags & $flag) ? true : false;
    }
    
    /**
     * Check if set using reopen instead of rewinddir
     *
     * @return bool
     */
    public function useReopen()     
    {
        return $this->_useReopen;
    }

    /**
     * Set using reopen instead of rewinddir
     *
     * @param bool $useReopen
     * @return Akt_Filesystem_Iterator_DirectoryIterator 
     */
    public function setUseReopen($useReopen)
    {
        $this->_useReopen = $useReopen !== null ? (bool) $useReopen : null;
        return $this;
    }
}