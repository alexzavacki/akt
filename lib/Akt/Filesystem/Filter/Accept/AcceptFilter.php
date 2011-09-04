<?php

/**
 * 
 */
interface Akt_Filesystem_Filter_Accept_AcceptFilter
{
    /**
     * Check if file should be kept
     * @param  string|SplFileInfo $file
     * @return bool
     */
    public function accept($file);
}