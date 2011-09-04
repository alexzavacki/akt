<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_Glob_GlobStaticIterator extends ArrayIterator
{
    /**
     * @const
     */
    const KEY_AS_PATHNAME = 0;    
    const KEY_AS_FILEINFO = 16;
    
    const CURRENT_AS_FILEINFO = 0;
    const CURRENT_AS_PATHNAME = 32;
    
    const UNIX_PATHS = 8192;

    /**
     * Iterator flags
     * @var int
     */
    protected $_flags = 0;
        
    
    /**
     * Constructor.
     * 
     * Wrapper for parent's method. Creates array from param if it's not
     * 
     * @param mixed $array
     * @param int $flags
     */
    public function __construct($array, $flags = 0)
    {
        if (!is_array($array)) {
            $array = array($array);
        }
        $this->_flags = (int) $flags;
        
        parent::__construct($array);
    }
    
    /**
     * Get filename as glob's pattern value
     * 
     * @return string
     */
    public function current()
    {
        if ($this->hasFlag(self::CURRENT_AS_PATHNAME)) {
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
        if ($this->hasFlag(self::KEY_AS_FILEINFO)) {
            return $this->getFileInfo();
        }
        return $this->getPathname();
    }
    
    /**
     * Get current file's info as instance of SplFileInfo
     * 
     * @return SplFileInfo
     */
    public function getFileInfo()
    {
        return new SplFileInfo($this->getPathname());
    }

    /**
     * Get full path to current file
     * 
     * @return string
     */
    public function getPathname()
    {
        $current = parent::current();
        
        if ($current instanceof Akt_Filesystem_Filter_Accept_Pathname_Glob) {
            /** @var $current Akt_Filesystem_Filter_Accept_Pathname_Glob */
            $current = $current->getFullPattern();
        }
        
        $dirsep = $this->hasFlag(self::UNIX_PATHS) 
            ? Akt_Filesystem_Path::DIRSEP_UNIX
            : null;
        $dirsep = Akt_Filesystem_Path::getDirectorySeparator($current, $dirsep);
        
        return strtr($current, '/\\', str_repeat($dirsep, 2));        
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
     * @return Akt_Filesystem_Iterator_Glob_GlobStaticIterator
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
}