<?php

/**
 * 
 */
class Akt_Options
{
    /**
     * Options storage
     * @var array
     */
    protected $_options = array();
    
    /**
     * Current alias delimiters
     * @var array|null 
     */
    protected $_aliasDelimiters;
    
    /**
     * Default aliases delimiters
     * @var array
     */
    protected static $_defaultAliasDelimiters = array('|', ',', ';');


    /**
     * Constructor.
     *
     * @param array $options 
     * @param mixed $defaultKey
     */
    public function __construct($options = array(), $defaultKey = 0) 
    {
        if (!is_array($options)) {
            $options = array($defaultKey => $options);
        }
        $this->_options = $options;
    }

    /**
     * Get option value or all options if $name is not passed or null
     *
     * It's possible to pass option key as array of keys
     * or as string of aliases separated by alias delimiters (e.g. '|'):
     *     array('host', 'hostname', 'remote')
     *     'host|hostname|remote'
     * or both:
     *     array('host|hostname', 'remote')
     *
     * If option key doesn't exist, $default will be returned
     *
     * @param string|null $option
     * @param mixed $default
     * @return mixed
     */
    public function get($option = null, $default = null)
    {
        if ($option === null) {
            return $this->_options;
        }
        
        if (!is_array($option)) {
            $option = array($option);
        }
        
        if (is_array($this->_aliasDelimiters)) {
            $delimiters = $this->_aliasDelimiters;
        }
        elseif (is_string($this->_aliasDelimiters)) {
            $delimiters = array($this->_aliasDelimiters);
        }
        elseif ($this->_aliasDelimiters === null && 
            is_array(self::$_defaultAliasDelimiters)) 
        {
            $delimiters = self::$_defaultAliasDelimiters;
        }
        else {
            $delimiters = array();
        }
        
        $delimiters = array_unique($delimiters);
        $delimiter = count($delimiters) ? array_pop($delimiters) : null;

        foreach ($option as $optionKey) 
        {
            if (count($delimiters)) {
                $optionKey = str_replace($delimiters, $delimiter, $optionKey);
            }
            
            $keyAliases = $delimiter !== null 
                ? explode($delimiter, $optionKey) 
                : array($optionKey);
            
            foreach ($keyAliases as $key) {
                if (array_key_exists($key, $this->_options)) {
                    return $this->_options[$key];
                }
            }
        }

        return $default;
    }
    
    /**
     * Set option value or array of values
     *
     * @param  string|array|Akt_Options $options
     * @param  mixed $valueOrOverwrite
     * @param  bool $overwrite
     * @return Akt_Options
     */
    public function set($options, $valueOrOverwrite = null, $overwrite = true)
    {
        $isSingleOption = false;
        
        if ($options instanceof self) {
            $options = $options->get();
        }
        elseif (!is_array($options)) {
            $options = array($options => $valueOrOverwrite);
            $isSingleOption = true;
        }
    
        if (!$isSingleOption && is_bool($valueOrOverwrite)) {
            $overwrite = $valueOrOverwrite;
        }            

        foreach ($options as $key => $value) {
            $key = $this->_key($key);
            if (!array_key_exists($key, $this->_options) || $overwrite) {
                $this->_options[$key] = $value;
            }
        }

        return $this;
    }
    
    /**
     * Merge options with overwrite
     * 
     * @param  string|array|Akt_Options $options
     * @return Akt_Options
     */
    public function merge($options)
    {
        $this->set($options);
        return $this;
    }

    /**
     * Check if option exists
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        $name = $this->_key($name);
        return array_key_exists($name, $this->_options);
    }
    
    /**
     * Clear all options
     *
     * @return Akt_Options 
     */
    public function clear()
    {
        $this->_options = array();
        return $this;
    }

    /**
     * Get internal item name for store
     *
     * @param string $name
     * @return string
     */
    protected function _key($name)
    {
        return strtolower(trim((string) $name));
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return $this->get(null);
    }
    
    /**
     * Get current alias delimiters
     *
     * @return array|null 
     */
    public function getAliasDelimiters()
    {
        return $this->_aliasDelimiters;
    }

    /**
     * Set current alias delimiters
     *
     * @param array|false $delimiters
     * @return Akt_Options 
     */
    public function setAliasDelimiters($delimiters)
    {
        $this->_aliasDelimiters = $delimiters;
        return $this;
    }

    /**
     * Get default alias delimiters
     *
     * @return array|null 
     */
    public static function getDefaultAliasDelimiters()
    {
        return self::$_defaultAliasDelimiters;
    }

    /**
     * Set default alias delimiters
     *
     * @param array $delimiters
     * @return Akt_Options 
     */
    public static function setDefaultAliasDelimiters($delimiters)
    {
        self::$_defaultAliasDelimiters = (array) $delimiters;
    }
}
