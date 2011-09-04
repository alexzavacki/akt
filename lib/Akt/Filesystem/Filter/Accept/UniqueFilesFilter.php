<?php

/**
 * 
 */
class Akt_Filesystem_Filter_Accept_UniqueFilesFilter
    extends Akt_Filesystem_Filter_Accept_InArrayFilter
{
    /**
     * Check if file should be kept
     * 
     * @param  string|SplFileInfo $file
     * @return bool
     */
    public function accept($file)
    {
        $file = $this->getPathname($file);
        
        $result = !parent::accept($file);
        if ($result) {
            $this->add($file);
        }
        
        return $result;
    }
}