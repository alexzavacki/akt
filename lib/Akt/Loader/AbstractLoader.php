<?php

/**
 * 
 */
abstract class Akt_Loader_AbstractLoader implements Akt_Loader_LoaderInterface
{
    /**
     * Try to load resource (class or interface) in specified file
     *
     * @param string $class
     * @param string $filename
     * @return bool
     */
    public static function loadResource($class, $filename)
    {
        try {
            self::loadFile($filename);

            if (!class_exists($class, false) && !interface_exists($class, false)) {
                throw new Akt_Exception("Class (or interface) '{$class}' not found"
                    . " in specified file '{$filename}'");
            }
        }
        catch (Akt_Exception $e) {
            return false;
        }

        return $class;
    }

    /**
     * Loads file by its filename
     *
     * @param string $filename 
     * @return void
     */
    public static function loadFile($filename)
    {
        if (!is_string($filename)) {
            throw new Akt_Exception("Filename must be a string");
        }

        $loaded = @include_once($filename);

        if ($loaded === false) {
            throw new Akt_Exception("File '{$filename}' not found or couldn't be read");
        }
    }
}
