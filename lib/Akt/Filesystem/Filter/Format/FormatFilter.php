<?php

/**
 * 
 */
interface Akt_Filesystem_Filter_Format_FormatFilter
{
    /**
     * Format iterator's current file name
     * @param  string|SplFileInfo $file
     * @return string
     */
    public function format($file);
}