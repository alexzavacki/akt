<?php

/** We need path functions */
require_once 'Akt/Filesystem/Path.php';

/**
 *
 */
class Akt_Filesystem_Dir
{
    /**
     * Create directory
     *
     * @param string $path
     * @param int $chmod
     * @return bool
     */
    public static function create($path, $chmod = 0777)
    {
        $path = Akt_Filesystem_Path::realize($path);

        if (self::exists($path)) {
            return true;
        }

        return mkdir($path, $chmod, true);
    }

    /**
     * Recreate directory
     *
     * Sets new chmod if passed
     *
     * @param string $path
     * @param int $chmod
     * @return bool
     */
    public static function recreate($path, $chmod = null)
    {
        if (!is_string($path) || trim($path) == '') {
            throw new Exception('Directory must be non-empty string');
        }

        if ($chmod === null && ($perms = @fileperms($path))) {
            $chmod = substr(decoct($perms), -4);
        }
        $chmod = $chmod ? $chmod : 0777;

        if (is_dir($path)) {
            if (!self::remove($path) || is_dir($path)) {
                throw new Exception("Error occurred during the removing of the directory {$path}");
            }
        }

        return self::create($path, $chmod);
    }

    /**
     * Check if dir exists
     *
     * @param string $path
     * @return bool
     */
    public static function exists($path)
    {
        return is_dir(realpath($path));
    }

    /**
     * Remove the directory
     *
     * @param string $path
     * @return bool
     */
    public static function remove($path, $recursive = true)
    {
        if (!is_string($path) || trim($path) == '') {
            throw new Exception('Directory must be non-empty string');
        }

        $path = realpath($path);

        if (!is_dir($path)) {
            return true;
        }

        if ($recursive) {
            foreach (scandir($path) as $entry)
            {
                if ($entry != '.' && $entry != '..') {
                    $path = $path . '/' . $entry;
                    if (is_dir($path)) {
                        self::remove($path);
                    }
                    elseif (is_file($path)) {
                        unlink($path);
                    }
                }
            }
        }

        return rmdir($path);
    }

    /**
     * Check if directory is empty
     *
     * @param string $path
     * @return bool
     */
    public static function isEmpty($path)
    {
        $path = realpath($path);

        if (!is_dir($path)) {
            throw new Exception("Directory '$path' doesn't exist");
        }

        $files = @scandir($path);
        return !(isset($files) && is_array($files) && count($files) > 2);
    }
}