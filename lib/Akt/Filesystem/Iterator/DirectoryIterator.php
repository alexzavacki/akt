<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_DirectoryIterator implements Iterator
{
    /**
     * @const
     */
    const KEY_AS_PATHNAME = 0;    
    const KEY_AS_FILEINFO = 16;
    
    const CURRENT_AS_FILEINFO = 0;
    const CURRENT_AS_PATHNAME = 32;
    
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
    protected $_flags = self::SKIP_DOTS;
    
    /**
     * Opened dir resource
     * @var resource
     */
    protected $_dirHandle;
    
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
    protected $_useReopenForRewind = null;
    
    /**
     * Defined and cached directory separator for current path
     * @var string
     */
    protected $_directorySeparator;
    
    /**
     * Info for current file
     * @var SplFileInfo
     */
    protected $_fileinfo;
    
    /**
     * Pathname for current file
     * @var string
     */
    protected $_pathname;
    
    
    /**
     * Constructor.
     * 
     * @param string $path
     * @param int $flags
     */
    public function __construct($path, $flags = null)
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
        // getDirectorySeparator() caches dirsep only for null as $path 
        $dirsep = ($path === null && is_string($this->_directorySeparator))
            ? $this->_directorySeparator
            : $this->getDirectorySeparator($path);
        
        // ... and now we can get current internal path, if $path is null
        if ($path === null) {
            $path = $this->_path;
        }
        
        if (!is_string($path)) {
            throw new Akt_Exception("Path must be a string");
        }
        
        $path = rtrim($path, "/\\") . $dirsep;
        
        if (!is_dir($path)) {
            throw new Akt_Exception("Path '{$path}' is not directory");
        }
        
        $this->_dirHandle = opendir($path);
        
        if (!is_resource($this->_dirHandle)) {
            throw new Akt_Exception("Path '{$path}' can't be read");
        }

        return $this->_dirHandle;
    }

    /**
     * Close dir and remove resource
     * 
     * @return void
     */
    protected function _close()
    {
        if (is_resource($this->_dirHandle)) {
            closedir($this->_dirHandle);
        }
        $this->_dirHandle = null;
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
        rewinddir($this->getDirHandle());
    }

    /**
     * Rewind the Iterator to the first element
     * 
     * @return void
     */
    public function rewind()
    {
        // rewinddir (seeking) doesn't work for some stream wrappers (e.g. ssh2.sftp)
        if ($this->_useReopenForRewind === null && Akt_Filesystem_Path::isStreamWrapped($this->_path)
            || $this->_useReopenForRewind
        ) {
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
     * Depends on iterator flags. By default file info will be returned
     *
     * @return SplFileInfo|string
     */
    public function current()
    {
        if ($this->_flags & self::CURRENT_AS_PATHNAME) {
            return $this->getPathname();
        }
        return $this->getFileInfo();
    }
    
    /**
     * Return the key of the current element
     * 
     * Depends on iterator flags. By default path name will be returned
     * 
     * @return string|SplFileInfo
     */
    public function key()
    {
        if ($this->_flags & self::KEY_AS_FILEINFO) {
            return $this->getFileInfo();
        }
        return $this->getPathname();
    }
    
    /**
     * Get or create dir resource
     *
     * @return resource|null
     */
    public function getDirHandle()
    {
        if (is_resource($this->_dirHandle)) {
            return $this->_dirHandle;
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
        $dirHandle = $this->getDirHandle();
        $skipDots = $this->_flags & self::SKIP_DOTS;
        
        do {
            $this->_currentFile = readdir($dirHandle);
        } 
        while ($skipDots && ($this->_currentFile == '.' || $this->_currentFile == '..'));
        
        $this->_position = $this->_position !== null ? $this->_position + 1 : 0;

        $this->_fileinfo = null;
        $this->_pathname = null;
                
        return $this->_currentFile;
    }
    
    /**
     * Check if file is '.' or '..'
     * 
     * @param  string $file
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
     * Get current dir path
     *
     * @return string 
     */
    public function getPath()     
    {
        return $this->_path;
    }
    
    /**
     * Get full path to current file
     * 
     * @return string
     */
    public function getPathname()
    {
        if (is_string($this->_pathname)) {
            return $this->_pathname;
        }
        
        $dirsep = is_string($this->_directorySeparator)
            ? $this->_directorySeparator
            : $this->getDirectorySeparator();
        
        return $this->_pathname = $this->_path . $dirsep . $this->getFile();
    }

    /**
     * Get current file's info as instance of SplFileInfo
     * 
     * @return SplFileInfo
     */
    public function getFileInfo()
    {
        if ($this->_fileinfo instanceof SplFileInfo) {
            return $this->_fileinfo;
        }
        return $this->_fileinfo = $this->_createFileInfo();
    }
    
    /**
     * Create SplFileInfo object for current file
     * 
     * @return SplFileInfo
     */
    protected function _createFileInfo()
    {
        return new SplFileInfo($this->getPathname());
    }
    
    /**
     * Get current directory separator
     * 
     * See Akt_Filesystem_Path::getDirectorySeparator() for detailed information
     *
     * @param  string $path
     * @return string 
     */
    public function getDirectorySeparator($path = null)
    {
        $isInternalPath = $path === null;
        
        if ($isInternalPath) {
            // dirsep may be already cached for internal path
            if (is_string($this->_directorySeparator)) {
                return $this->_directorySeparator;
            }
            $path = $this->_path;
        }
        
        $dirsep = $this->hasFlag(self::UNIX_PATHS) 
            ? Akt_Filesystem_Path::DIRSEP_UNIX
            : null;
        
        $dirsep = Akt_Filesystem_Path::getDirectorySeparator($path, $dirsep);
        
        if ($isInternalPath) {
            $this->_directorySeparator = $dirsep;
        }
        
        return $dirsep;
    }
    
    /**
     * Get current directory separator
     * 
     * Short alias of getDirectorySeparator()
     * 
     * @param  string $path
     * @return string 
     */
    public function dirSeparator($path = null)
    {
        return $this->getDirectorySeparator($path);
    }
    
    /**
     * Get current iterator position
     * 
     * @return int
     */
    public function getPosition()
    {
        return $this->_position;
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
    public function useReopenForRewind()     
    {
        return $this->_useReopenForRewind;
    }

    /**
     * Set using reopen instead of rewinddir
     *
     * @param bool $useReopen
     * @return Akt_Filesystem_Iterator_DirectoryIterator 
     */
    public function setUseReopenForRewind($useReopen)
    {
        $this->_useReopenForRewind = $useReopen !== null ? (bool) $useReopen : null;
        return $this;
    }
}