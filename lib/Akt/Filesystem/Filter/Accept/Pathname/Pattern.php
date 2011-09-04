<?php

/**
 * 
 */
abstract class Akt_Filesystem_Filter_Accept_Pathname_Pattern
    extends Akt_Filesystem_Filter_Accept_Pathname_AbstractPathnameFilter
{
    /**
     * Filter pattern
     * @var array
     */
    protected $_pattern;


    /**
     * Constructor.
     *
     * @param string $pattern 
     * @param string $basedir
     */
    public function __construct($pattern, $basedir = null)
    {
        $this->_pattern = $pattern;
        parent::__construct($basedir);
    }
    
    /**
     * Get full pattern with prefixed basedir if it is set
     * 
     * @return false|string
     */
    public function getFullPattern()
    {
        $file = $this->_pattern;
        
        if (is_string($this->_basedir)) {
            $file = rtrim($this->_basedir, '/\\') . "/$file";
        }
        
        return $file;
    }
    
    /**
     * Get current filter pattern
     *
     * @return string 
     */
    public function getPattern()     
    {
        return $this->_pattern;
    }

    /**
     * Set filter pattern
     *
     * @param string $pattern
     * @return Akt_Filesystem_Filter_Accept_Pathname_Pattern 
     */
    public function setPattern($pattern)
    {
        $this->_pattern = $pattern;
        return $this;
    }
}