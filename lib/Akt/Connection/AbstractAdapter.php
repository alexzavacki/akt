<?php

abstract class Akt_Connection_AbstractAdapter
{
    /**
     * Parent connection
     * @var Akt_Connection_Connection
     */
    protected $_connection;

    /**
     * Own connection handle
     * @var mixed
     */
    protected $_connectionHandle;

    /**
     * Connection options
     * @var array
     */
    protected $_options = array();

    
    /**
     * Set parent connection
     *
     * @param Akt_Connection_Connection|null $connection
     * @return Akt_Connection_AbstractAdapter
     */
    public function setConnection($connection)
    {
        $this->_connection = $connection;
        return $this;
    }

    /**
     * Get parent connection
     *
     * @return Akt_Connection_Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Set own connection handle
     *
     * @param mixed $handle
     * @return Akt_Connection_AbstractAdapter
     */
    public function setConnectionHandle($handle)
    {
        $this->_connectionHandle = $handle;
        return $this;
    }

    /**
     * Get own connection handle
     *
     * @return mixed
     */
    public function getConnectionHandle()
    {
        return $this->_connectionHandle;
    }

    /**
     * Set option value
     *
     * @param string $option
     * @param mixed $value
     * @param bool $overwrite
     * @return Akt_Connection_AbstractAdapter
     */
    public function setOption($option, $value, $overwrite = true)
    {
        $option = (string) $option;

        if (!array_key_exists($this->_options[$option]) || $overwrite) {
            $this->_options[$option] = $value;
        }
        
        return $this;
    }

    /**
     * Set an array of options
     *
     * @param array $options
     * @return Akt_Connection_AbstractAdapter
     */
    public function setOptions($options)
    {
        if (!is_array($options)) {
            throw new Akt_Exception('Only array of options can be set in this method');
        }

        $this->_options = array_merge($this->_options, $options);
        return $this;
    }

    /**
     * Get option value
     *
     * It's possible to pass option key as array of keys
     * or as string of aliases separated by '|', ',' or ';':
     *     array('host', 'hostname', 'remote')
     *     'host|hostname|remote'
     * or both:
     *     array('host|hostname', 'remote')
     *
     * If option key doesn't exist in adapter options, $default will be returned
     *
     * @param string|array $option
     * @param mixed $default
     * @return mixed
     */
    public function getOption($option, $default = null)
    {
        if (!is_array($option)) {
            $option = array($option);
        }

        foreach ($option as $optionKey) {
            $keyAliases = explode('|', str_replace(array(',', ';'), '|', $optionKey));
            foreach ($keyAliases as $key) {
                if (array_key_exists($key, $this->_options)) {
                    return $this->_options[$key];
                }
            }
        }

        return $default;
    }
}