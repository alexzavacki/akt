<?php

/**
 * 
 */
abstract class Akt_Filesystem_Sync_Adapter_AbstractAdapter
    implements Akt_Filesystem_Sync_Adapter_SyncAdapter
{
    /**
     * Sync options
     * @var Akt_Options 
     */
    protected $_options;
    
    
    /**
     * Set adapter params
     * @abstract
     * @param  array $options
     * @return Akt_Filesystem_Sync_Adapter_SyncAdapter
     */
    public function setOptions($options)
    {
        if ($options instanceof Akt_Options) {
            $options = $options->toArray();
        }
        elseif (!is_array($options)) {
            throw new Akt_Exception('Options must be an array or instance of Akt_Options');
        }

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
            else {
                $this->options()->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * Get options adapter
     *
     * @return Akt_Options
     */
    public function options()
    {
        if (!$this->_options instanceof Akt_Options) {
            $this->_options = new Akt_Options();
        }
        return $this->_options;
    }
}