<?php

interface Akt_Loader_LoaderInterface
{
    /**
     * Load class or interface
     * @param string $class
     * @return string|false
     */
    public static function load($class);

    /**
     * Get file location by resource name
     * @param string $class
     * @return string
     */
    public static function getResourceFilename($class);
}
