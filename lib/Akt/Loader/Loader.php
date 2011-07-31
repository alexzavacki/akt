<?php

require_once dirname(__FILE__) . '/LoaderInterface.php';
require_once dirname(__FILE__) . '/AbstractLoader.php';

/**
 * Akt_Loader_Loader
 * Loads Akt and PEAR-like classes
 */
class Akt_Loader_Loader extends Akt_Loader_AbstractLoader
{
    /**
     * Loads Akt class or interface
     *
     * @param string $class
     * @return string|false
     */
    public static function load($class)
    {
        return self::loadClass($class, self::getClassFilename($class)) ? $class : false;
    }

    /**
     * Get Akt class file
     *
     * @param string $class
     * @return string|false
     */
    public static function getClassFilename($class)
    {
        return str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
    }
}
