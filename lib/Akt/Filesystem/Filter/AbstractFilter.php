<?php

/**
 * 
 */
abstract class Akt_Filesystem_Filter_AbstractFilter
    implements Akt_Filesystem_Filter_Filter
{
    /**
     * Get file's info as instance of SplFileInfo
     * 
     * @param  string|SplFileInfo $file
     * @return SplFileInfo
     */
    public function getFileInfo($file)
    {
        if (is_string($file)) {
            $file = new SplFileInfo($file);
        }
        elseif (!$file instanceof SplFileInfo) {
            throw new Akt_Exception("Can't get info for {$file}");
        }
        return $file;
    }
    
    /**
     * Get full path name
     * 
     * @param  string|SplFileInfo $file
     * @return string
     */
    public function getPathname($file)
    {
        if ($file instanceof SplFileInfo) {
            return $file->getPathname();
        }
        return $file;
    }
}