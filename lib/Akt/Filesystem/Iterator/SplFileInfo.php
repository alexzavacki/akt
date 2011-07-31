<?php

/**
 * Extends SplFileInfo to support relative paths
 */
class Akt_Filesystem_Iterator_SplFileInfo extends SplFileInfo 
{
    /**
     * @var string
     */    
    protected $_relativePathname;

    
    /**
     * Constructor
     *
     * @param string $file             The file name
     * @param string $relativePathname The relative path name
     */
    public function __construct($file, $relativePathname = null)
    {
        parent::__construct($file);
        $this->_relativePathname = $relativePathname;
    }

    /**
     * Returns the relative path name
     *
     * @return string
     */
    public function getRelativePathname()
    {
        return $this->_relativePathname;
    }

    /**
     * Returns the relative path
     *
     * @return string
     */
    public function getRelativePath()
    {
        if (!is_string($this->_relativePathname) || $this->_relativePathname == '') {
            return '';
        }
        return ($dirname = dirname($this->_relativePathname)) == '.' ? '' : $dirname;
    }
}