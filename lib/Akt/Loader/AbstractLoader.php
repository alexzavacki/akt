<?php

/**
 * 
 */
abstract class Akt_Loader_AbstractLoader implements Akt_Loader_LoaderInterface
{
    /**
     * Last cached include path
     * @var string
     */
    protected static $_includePath;

    /**
     * Parts of the last cached include path
     * @var array
     */
    protected static $_includePathParts;
    
    
    /**
     * Try to load resource (class or interface) in specified file
     *
     * @param string $class
     * @param string $filename
     * @return bool
     */
    public static function loadClass($class, $filename)
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
        
        $loaded = false;
        if (self::isReadable($filename)) {
            $loaded = include_once($filename);
        }

        if ($loaded === false) {
            throw new Akt_Exception("File '{$filename}' not found or couldn't be read");
        }
    }
    /**
     * Check if file is readable (uses include path)
     *
     * @param  string $filename
     * @return bool
     */
    public static function isReadable($filename)
    {
        $currentIncludePath = get_include_path();

        if (!is_array(self::$_includePathParts)
            || self::$_includePath != $currentIncludePath
        ) {
            self::$_includePath = $currentIncludePath;
            self::$_includePathParts = self::explodeIncludePath($currentIncludePath);
        }

        foreach (self::$_includePathParts as $path)
        {
            if ($path == '.') {
                if (is_readable($filename)) {
                    return true;
                }
                continue;
            }
            $file = $path . DIRECTORY_SEPARATOR . $filename;
            if (is_readable($file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Explode an include path into an array
     *
     * @param  string|null $path
     * @return array
     */
    public static function explodeIncludePath($path = null)
    {
        if ($path === null) {
            $path = get_include_path();
        }

        $paths = PATH_SEPARATOR == ':'
            ? preg_split('#:(?!//)#', $path)
            : explode(PATH_SEPARATOR, $path);
        
        return $paths;
    }
}
