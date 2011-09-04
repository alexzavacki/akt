<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_Glob_GlobRecursiveDirectoryIterator 
    extends Akt_Filesystem_Iterator_RecursiveDirectoryIterator 
{
    /**
     * Glob filter
     * @var Akt_Filesystem_Filter_Accept_Pathname_Glob
     */
    protected $_glob;
    
    /**
     * Directories structure cache
     * @var Akt_Filesystem_Cache_DirectoryTreeCache
     */
    protected $_directoryTreeCache;
    
    
    /**
     * Constructor.
     * 
     * @throws Akt_Exception
     * 
     * @param string|Akt_Filesystem_Filter_Accept_Pathname_Glob $glob
     * @param int $flags
     * @param Akt_Filesystem_Cache_DirectoryTreeCache $directoryTreeCache
     * @param string $path
     * @param string $subpath
     */
    public function __construct($glob, $flags = null, $directoryTreeCache = null, 
        $path = null, $subpath = null
    ) {
        if (is_string($glob)) {
            $glob = new Akt_Filesystem_Filter_Accept_Pathname_Glob($glob);
        }
        elseif (!$glob instanceof Akt_Filesystem_Filter_Accept_Pathname_Glob) {
            throw new Akt_Exception("Glob filter must be a string or an instance of Akt_Filesystem_Filter_Accept_Pathname_Glob");
        }
        $this->_glob = $glob;
        
        if ($directoryTreeCache instanceof Akt_Filesystem_Cache_DirectoryTreeCache) {
            $this->_directoryTreeCache = $directoryTreeCache;
        }
        
        if (!is_string($path)) {
            $path = $this->_getMaxStaticPath($glob->getFullPattern());
        }
        
        parent::__construct($path, $flags, $subpath);
    }
    
    /**
     * Get max static path to dir that can be recursively scanned
     * 
     * @param  string $path
     * @return string
     */
    protected function _getMaxStaticPath($path)
    {
        $magicPos = strpos($path, '*');
        if ($magicPos !== false) {
            $offset = strlen($path) - $magicPos;
            $slashPos = strrpos(strtr($path, '\\', '/'), '/', -$offset);
            if ($slashPos !== false) {
                $path = substr($path, 0, $slashPos + 1);
            }
        }
        return $path;
    }
    
    /**
     * Returns an iterator for the current file if it is a directory
     * 
     * @return Akt_Filesystem_Iterator_Glob_GlobRecursiveDirectoryIterator
     */
    public function getChildren() 
    {
        $path = $this->getPathname();
        
        $subdir = new self($this->_glob, $this->_flags, $this->_directoryTreeCache, 
            $path, $this->getSubPathname()
        );
        $subdir->setUseReopenForRewind($this->_useReopenForRewind);
        
        return $subdir;
    }
}
