<?php

/**
 * 
 */
abstract class Akt_Filesystem_Filter_Accept_Pathname_AbstractPathnameFilter
    extends Akt_Filesystem_Filter_AbstractFilter
    implements Akt_Filesystem_Filter_Accept_AcceptFilter
{
    /**
     * Filter base dir
     * @var string
     */
    protected $_basedir;

    
    /**
     * Constructor.
     * 
     * @param string $basedir
     */
    public function __construct($basedir = null)
    {
        if (is_string($basedir)) {
            $this->setBasedir($basedir);
        }
    }
    
    /**
     * Get relative path name by removing basedir part from the beginning
     * 
     * @param  string|SplFileInfo $file
     * @return string
     */
    public function getRelativePathname($file)
    {
        $file = $this->getPathname($file);
        $file = Akt_Filesystem_Path::clean($file, '/');
        
        if (is_string($this->_basedir)) {
            if (strpos($file, $this->_basedir) === 0) {
                $file = ltrim(substr($file, strlen($this->_basedir)), '/\\');
            }
        }
        
        return $file;
    }
    
    /**
     * Set filter's base dir
     * 
     * @param  string $basedir
     * @return Akt_Filesystem_Filter_Accept_Pathname_AbstractPathnameFilter
     */
    public function setBasedir($basedir)
    {
        if (is_string($basedir)) {
            $basedir = rtrim(Akt_Filesystem_Path::clean($basedir, '/'), '/\\');
        }
        $this->_basedir = $basedir;
        return $this;
    }

    /**
     * Get filter's base dir
     * 
     * @return string
     */
    public function getBasedir()
    {
        return $this->_basedir;
    }
}