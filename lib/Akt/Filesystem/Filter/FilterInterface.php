<?php

/**
 * 
 */
interface Akt_Filesystem_Filter_FilterInterface
{
    /**
     * Check if file should be kept
     *
     * @param SplFileInfo $fileinfo
     * @return bool 
     */    
    public function accept($fileinfo);
}