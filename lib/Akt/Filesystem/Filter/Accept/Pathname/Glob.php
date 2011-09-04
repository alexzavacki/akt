<?php

/**
 * 
 */
class Akt_Filesystem_Filter_Accept_Pathname_Glob 
    extends Akt_Filesystem_Filter_Accept_Pathname_Pattern
{
    /**
     * Compiled regex for current pattern
     * @var string
     */
    protected $_patternRegex;
    
    
    /**
     * Check if file should be kept
     * 
     * @param  string|SplFileInfo $file
     * @return bool
     */
    public function accept($file)
    {
        $file = $this->getRelativePathname($file);
        
        $regex = is_string($this->_patternRegex) 
            ? $this->_patternRegex 
            : ($this->_patternRegex = Akt_Filesystem_Path_Glob::toRegex($this->_pattern));
        
        return preg_match($regex, $file) ? true : false;
    }
    
    /**
     * Get full pattern with prefixed basedir if it is set
     * 
     * @return false|string
     */
    public function getFullPattern()
    {
        return Akt_Filesystem_Path::realize(parent::getFullPattern());
    }
    
    /**
     * Set filter pattern
     *
     * @param string $pattern
     * @return Akt_Filesystem_Filter_Accept_Pathname_Glob 
     */
    public function setPattern($pattern)
    {
        parent::setPattern($pattern);
        $this->_patternRegex = null;
        return $this;
    }    
}