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
    public function __construct($path, $flags = 0, $subpath = null)
    {
        $this->_subpath = $subpath;
        parent::__construct($path, $flags);
    }
    
    /**
     * Return the current element
     * 
     * Return information about current file as instance of Akt_Filesystem_Iterator_SplFileInfo
     *
     * @return Akt_Filesystem_Iterator_SplFileInfo
     */
    public function current()
    {
        return new Akt_Filesystem_Iterator_SplFileInfo(
            parent::current()->getPathname(), $this->getSubPath(), $this->getSubPathname()
        );
    }
    
    /**
     * Returns whether current file is a directory and not '.' or '..'
     *
     * @param bool $allowLinks
     * @return bool 
     */
    public function hasChildren($allowLinks = false) 
    {
        if ($this->isDot()) {
            return false;
        }
        
        $path = parent::current()->getPathname();
        
        if (!$allowLinks && !$this->hasFlag(self::FOLLOW_SYMLINKS)) {
            if (is_link($path)) {
                return false;
            }
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
        $path = parent::current()->getPathname();
        
        $subpath = $this->getFile();
        if (is_string($this->_subpath)) {
            $subpath = $this->_subpath . $this->dirSeparator() . $subpath;
        }
        
        // @todo: rewrite this with __clone()?
        $subdir = new self($path, $this->_flags, $subpath);
        $subdir->setUseReopen($this->_useReopen);
        
        return $subdir;
    } 
    
    /**
     * Get sub path
     *
     * @return string 
     */
    public function getSubPath() 
    {
        return $this->_subpath !== null ? $this->_subpath : '';
    }
    
    /**
     * Get sub path and file name
     * 
     * @return string
     */
    public function getSubPathname() 
    {
        $subPathName = $this->getFile();
        if ($this->_subpath !== null) {
            $subPathName = $this->_subpath . $this->dirSeparator() . $subPathName;
        }
        return $subPathName;
    }
}