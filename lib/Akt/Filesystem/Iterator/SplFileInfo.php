<?php

/**
 * Extends SplFileInfo to support relative paths
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Akt_Filesystem_Iterator_SplFileInfo extends SplFileInfo 
{
    /**
     * @var string
     */
    protected $_relativePath;
    
    /**
     * @var string
     */    
    protected $_relativePathname;

    
    /**
     * Constructor
     *
     * @param string $fileInfo         The file name
     * @param string $relativePath     The relative path
     * @param string $relativePathname The relative path name
     */
    public function __construct($file, $relativePath = null, $relativePathname = null)
    {
        parent::__construct($file);
        $this->_relativePath = $relativePath;
        $this->_relativePathname = $relativePathname;
    }

    /**
     * Returns the relative path
     *
     * @return string
     */
    public function getRelativePath()
    {
        return $this->_relativePath;
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
}