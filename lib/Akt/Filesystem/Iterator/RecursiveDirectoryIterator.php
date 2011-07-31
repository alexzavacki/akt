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
     * Get current file's info as instance of Akt_Filesystem_Iterator_SplFileInfo
     * 
     * @return SplFileInfo
     */
    public function getFileInfo()
    {
        return new Akt_Filesystem_Iterator_SplFileInfo(
            parent::getFileInfo()->getPathname(), $this->getSubPathname()
        );        
    }

    /**
     * Returns whether current file is a directory and not '.' or '..'
     *
     * @param  bool $allowLinks
     * @return bool 
     */
    public function hasChildren($allowLinks = false) 
    {
        if ($this->isDot()) {
            return false;
        }
        
        $path = $this->getFileInfo()->getPathname();
        
        if (is_link($path) && !$allowLinks && !$this->hasFlag(self::FOLLOW_SYMLINKS)) {
            return false;
        }
        
        return is_dir($path);
    } 
    
    /**
     * Returns an iterator for the current file if it is a directory
     * 
     * @return Akt_Filesystem_Iterator_RecursiveDirectoryIterator
     */
    public function getChildren() 
    {
        $path = $this->getFileInfo()->getPathname();
        
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
        if (is_string($this->_subpath)) {
            $subPathName = $this->_subpath . $this->getDirectorySeparator() . $subPathName;
        }
        return $subPathName;
    }
}