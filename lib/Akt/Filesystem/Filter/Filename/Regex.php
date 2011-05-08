<?php

/**
 * 
 */
class Akt_Filesystem_Filter_Filename_Regex
    extends Akt_Filesystem_Filter_Filename_Pattern
{
    /**
     * Check if file should be kept
     *
     * @param SplFileInfo $fileinfo
     * @return bool 
     */    
    public function accept($fileinfo)
    {
        return preg_match($this->_pattern, $this->getSubPathname($fileinfo)) ? true : false;
    }
}