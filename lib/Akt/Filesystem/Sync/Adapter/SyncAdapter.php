<?php

/**
 * 
 */
interface Akt_Filesystem_Sync_Adapter_SyncAdapter
{
    /**
     * Do sync
     * @abstract
     * @param mixed $target
     * @return void
     */
    public function sync($target = null);
    
    /**
     * Set adapter params
     * @abstract
     * @param  array $options
     * @return Akt_Filesystem_Sync_Adapter_SyncAdapter
     */
    public function setOptions($options);
}