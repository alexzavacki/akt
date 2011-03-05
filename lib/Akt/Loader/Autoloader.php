<?php

/** We need default Loader */
require_once 'Akt/Loader/Loader.php';

/**
 *
 */
class Akt_Loader_Autoloader
{
    /**
     * Autoloader instance
     * @var Akt_Autoloader
     */
    protected static $_instance;

    /**
     * Internal autoloader callback
     * @var array
     */
    protected $_internalAutoloader = array('Akt_Loader_Loader', 'load');

    /**
     * Required autoloaders for properly work
     * @var array
     */
    protected $_requiredAutoloaders = array();

    /**
     * Registered loaders (callbacks)
     * @var array
     */
    protected $_registeredAutoloaders = array();


    /**
     * Get autoloader instance
     *
     * @return Akt_Autoloader
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Remove autoloader instance
     *
     * @return void
     */
    public static function clearInstance()
    {
        self::$_instance = null;
    }

    /**
     * Constructor
     * Registers own static method "autoload" as SPL autoload callback
     */
    protected function __construct()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Destructor
     * Removes own autoload method from SPL autoload stack
     */
    public function  __destruct()
    {
        spl_autoload_unregister(array(__CLASS__, 'autoload'));
    }

    /**
     * Load file by class name
     *
     * @param string $class
     * @return bool
     */
    public static function autoload($class)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return true;
        }

        foreach (self::getInstance()->getAutoloaders() as $autoloader)
        {
            if (is_array($autoloader))
            {
                $object = array_shift($autoloader);
                $method = array_shift($autoloader);
                if (call_user_func(array($object, $method), $class)) {
                    return true;
                }
            }
            elseif (is_object($autoloader)) {
                if ($autoloader->autoload($class)) {
                    return true;
                }
            }
            elseif (is_string($autoloader)) {
                if ($autoloader($class)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get all autoloaders stack
     *
     * @return array
     */
    public function getAutoloaders()
    {
        return array_merge(
            array($this->_internalAutoloader),
            $this->_requiredAutoloaders,
            $this->_registeredAutoloaders
        );
    }

    /**
     * Get registered autoloaders
     *
     * @return array
     */
    public function getRegisteredAutoloaders()
    {
        return $this->_registeredAutoloaders;
    }

    /**
     * Unshift autoloader callback
     *
     * @param  object|array|string $callback
     * @return Akt_Autoloader
     */
    public function unshiftAutoloader($callback)
    {
        if (is_callable($callback)) {
            $callback = array($callback);
        }
        elseif (!is_array($callback)) {
            throw new Akt_Exception('Autoloader callback must be an array or callable');
        }

        foreach ($callback as $clbck) {
            array_unshift($this->_registeredAutoloaders, $clbck);
        }

        return $this;
    }

    /**
     * Push autoloader callback
     *
     * @param  object|array|string $callback
     * @return Akt_Autoloader
     */
    public function pushAutoloader($callback)
    {
        if (is_callable($callback)) {
            $callback = array($callback);
        }
        elseif (!is_array($callback)) {
            throw new Akt_Exception('Autoloader callback must be an array or callable');
        }

        foreach ($callback as $clbck) {
            array_push($this->_registeredAutoloaders, $clbck);
        }

        return $this;
    }

    /**
     * Remove autoloader callback
     *
     * @param  object|array|string $callback
     * @return Akt_Autoloader
     */
    public function removeAutoloader($callback)
    {
        $index = array_search($callback, $this->_registeredAutoloaders, true);
        
        if ($index !== false) {
            unset($this->_registeredAutoloaders[ $index ]);
        }

        return $this;
    }

    /**
     * Disable Akt short classnames like "dir", "config", etc.
     *
     * @param string|array $alias
     */
    public static function disableAlias($alias)
    {
        if (is_string($alias)) {
            $alias = array($alias);
        }
        elseif (!is_array($alias)) {
            throw new Akt_Exception('Alias name must be a string or array of strings');
        }

        // ...
    }
}