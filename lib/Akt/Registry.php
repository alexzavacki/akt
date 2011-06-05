<?php

/**
 * 
 */
class Akt_Registry
{
    /**
     * Registry storage
     * @var array
     */
    protected static $_storage = array();

    /**
     * Singleton
     */
    protected function __construct() {}
    protected function __clone() {}

    /**
     * Get item
     *
     * @param string $name
     * @param mixed $default
     * @return object|null
     */
    public static function get($name, $default = null)
    {
        $name = self::_name($name);
        return self::has($name) ? self::$_storage[$name] : $default;
    }

    /**
     * Check if item is registered
     *
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        $name = self::_name($name);
        return array_key_exists($name, self::$_storage);
    }

    /**
     * Check if item is registered
     *
     * @param string $name
     * @return bool
     */
    public static function isRegistered($name)
    {
        return self::has($name);
    }

    /**
     * Set item
     *
     * @param string $name
     * @param object $value
     * @param bool $overwrite
     * @return object|null
     */
    public static function set($name, $value, $overwrite = true)
    {
        $name = self::_name($name);

        if (self::has($name) && ($overwrite === false)) {
            return self::get($name);
        }

        self::$_storage[$name] = $value;
        return self::$_storage[$name];
    }

    /**
     * Get internal item name for store
     *
     * @param string $name
     * @return string
     */
    protected static function _name($name)
    {
        return strtolower(trim((string) $name));
    }
}
