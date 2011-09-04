<?php

/**
 * 
 */
abstract class Akt_Filesystem_Filter_Format_AbstractFormatFilter
    extends Akt_Filesystem_Filter_AbstractFilter
    implements Akt_Filesystem_Filter_Format_FormatFilter
{
    /**
     * Get path name
     * 
     * @param  string|SplFileInfo $file
     * @return string
     */
    public function getPathname($file)
    {
        if ($file instanceof SplFileInfo) {
            $file = $file->getPathname();
        }
        elseif (!is_string($file)) {
            throw new Akt_Exception("Can't get pathname for {$file}");
        }
        return $file;
    }
}