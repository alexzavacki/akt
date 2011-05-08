<?php

/**
 * 
 */
abstract class Akt_Filesystem_Filter_Filename_Pattern
    implements Akt_Filesystem_Filter_FilterInterface
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
     */
    public function __construct($pattern)
    {
        $this->_pattern = $pattern;
    }
    
    /**
     * 
     * @param SplFileInfo $fileinfo
     * @return string
     */
    public function getSubPathname($fileinfo)
    {
        return $fileinfo->getRelativePathname();
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
     * @return Akt_Filesystem_Filter_Filename_Pattern 
     */
    public function setPattern($pattern)
    {
        $this->_pattern = $pattern;
        return $this;
    }
}