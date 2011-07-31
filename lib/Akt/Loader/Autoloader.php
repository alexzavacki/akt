<?php

/** We need default internal and ClassAlias loaders */
require_once dirname(__FILE__) . '/Loader.php';
require_once dirname(__FILE__) . '/ClassAliasLoader.php';

/**
 *
 */
class Akt_Loader_Autoloader
{
    /**
     * Autoloader instance
     * @var Akt_Loader_Autoloader
     */
    protected static $_instance;

    /**
     * Internal autoloader callback
     * @var array
     */
    protected $_internalAutoloader = array('Akt_Loader_Loader', 'load');

    /**
     * Required autoloaders for properly work
     * They will be called before registered (can be used for vendor scripts loading)
     * @var array
     */
    protected $_requiredAutoloaders = array();

    /**
     * Registered loaders (callbacks)
     * @var array
     */
    protected $_registeredAutoloaders = array();

    /**
     * Aliases loader
     * @var array
     */
    protected $_classAliasAutoloader = array('Akt_Loader_ClassAliasLoader', 'load');


    /**
     * Get autoloader instance
     *
     * @return Akt_Loader_Autoloader
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
     * Register own static method "autoload" as SPL autoload callback
     */
    protected function __construct()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }

    /**
     * Destructor
     * Remove own autoload method from SPL autoload stack
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

        /** @var array|Akt_Loader_LoaderInterface|string $autoloader */
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
                if ($autoloader->load($class)) {
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
            $this->_registeredAutoloaders,
            array($this->_classAliasAutoloader)
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
     * @return Akt_Loader_Autoloader
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
     * @return Akt_Loader_Autoloader
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
     * @return Akt_Loader_Autoloader
     */
    public function removeAutoloader($callback)
    {
        $index = array_search($callback, $this->_registeredAutoloaders, true);
        
        if ($index !== false) {
            unset($this->_registeredAutoloaders[$index]);
        }

        return $this;
    }
}
