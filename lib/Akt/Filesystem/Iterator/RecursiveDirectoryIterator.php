<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_RecursiveDirectoryIterator 
    extends Akt_Filesystem_Iterator_DirectoryIterator implements RecursiveIterator
{
    /**
     * @var string 
     */
    protected $_subpath;
    

    /**
     * Constructor.
     * 
     * @param string $path
     * @param int $flags
     * @param string $subpath
     */
    public function __construct($path, $flags = null, $subpath = null)
    {
        $this->_subpath = $subpath;
        parent::__construct($path, $flags);
    }

    /**
     * Create SplFileInfo object for current file
     * 
     * @return SplFileInfo
     */
    protected function _createFileInfo()
    {
        return new Akt_Filesystem_Iterator_SplFileInfo(
            $this->getPathname(), $this->getSubPathname()
        );   
    }
    
    /**
     * Returns true if current file is a directory and not '.' or '..'
     *
     * @param  bool $allowLinks
     * @return bool 
     */
    public function hasChildren($allowLinks = false) 
    {
        if ($this->isDot()) {
            return false;
        }
        
        //$path = $this->getFileInfo()->getPathname();
        $path = $this->getPathname();
        
        if (!is_dir($path)) {
            return false;
        }
        
        if (is_link($path) && !$allowLinks && !$this->hasFlag(self::FOLLOW_SYMLINKS)) {
            // path is symlink and there is no follow link options
            return false;
        }
        
        return true;
    } 
    
    /**
     * Returns an iterator for the current file if it is a directory
     * 
     * @return Akt_Filesystem_Iterator_RecursiveDirectoryIterator
     */
    public function getChildren() 
    {
        //$path = $this->getFileInfo()->getPathname();
        $path = $this->getPathname();
        
        $subdir = new self($path, $this->_flags, $this->getSubPathname());
        $subdir->setUseReopenForRewind($this->_useReopenForRewind);
        
        return $subdir;
    } 
    
    /**
     * Get current subpath
     *
     * @return string 
     */
    public function getSubPath() 
    {
        return is_string($this->_subpath) ? $this->_subpath : '';
    }
    
    /**
     * Get full subpath name (i.e. subpath including filename)
     * 
     * @return string
     */
    public function getSubPathname() 
    {
        $subPathName = $this->getFile();
        
        if (is_string($this->_subpath)) 
        {
            $dirsep = is_string($this->_directorySeparator)
                ? $this->_directorySeparator
                : $this->getDirectorySeparator();
            
            $subPathName = $this->_subpath . $dirsep . $subPathName;
        }
        
        return $subPathName;
    }
}