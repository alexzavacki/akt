<?php

/**
 * 
 */
class Akt_Filesystem_Filter_Accept_Pathname_Regex
    extends Akt_Filesystem_Filter_Accept_Pathname_Pattern
{
    /**
     * Check if file should be kept
     * 
     * @param  string|SplFileInfo $file
     * @return bool
     */
    public function accept($file)
    {
        $file = $this->getRelativePathname($file);
        return preg_match($this->_pattern, $file) ? true : false;
    }
}