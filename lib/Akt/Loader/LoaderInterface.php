<?php

interface Akt_Loader_LoaderInterface
{
    /**
     * Load class or interface
     * @param string $class
     * @return string|false
     */
    public static function load($class);
}
