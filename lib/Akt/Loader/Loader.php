<?php

require_once 'Akt/Loader/LoaderInterface.php';
require_once 'Akt/Loader/AbstractLoader.php';

/**
 * Akt_Loader_Loader
 * Loads all Akt and PEAR-like classes
 */
class Akt_Loader_Loader extends Akt_Loader_AbstractLoader
{
    /**
     * Loads Akt class or interface
     *
     * Method for php <= 5.2, since it not support LSB
     *
     * @param string $class
     * @return string|false
     */
    public static function load($class)
    {
        return self::loadResource($class, self::getResourceFilename($class)) ? $class : false;
    }

    /**
     * Get Akt resource file
     *
     * @param string $class
     * @return string|false
     */
    public static function getResourceFilename($class)
    {
        return str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
    }
}
