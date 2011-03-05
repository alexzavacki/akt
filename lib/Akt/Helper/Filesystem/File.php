<?php

/** We need path and dir functions */
require_once 'Akt/Helper/Filesystem/Path.php';
require_once 'Akt/Helper/Filesystem/Dir.php';

/**
 *
 */
class Akt_Helper_Filesystem_File
{
    /**
     * Create file
     *
     * @param string $path
     * @param int $chmod
     * @return bool
     */
    public static function create($path, $chmod = 0777, $dirchmod = null)
    {
        $path = Akt_Helper_Filesystem_Path::clean($path);

        if (self::exists($path)) {
            return true;
        }

        $dir = dirname($path);
        if (!Akt_Helper_Filesystem_Dir::exists($dir)) {
            if (!$dirchmod) {
                $dirchmod = $chmod;
            }
            Akt_Helper_Filesystem_Dir::create($dir, $dirchmod);
        }

        if (is_writable($dir) && touch($path)) {
            chmod($path, $chmod);
            return true;
        }

        return false;
    }

    /**
     * Check if path exists and it's a directory
     *
     * @param string $path
     * @return bool
     */
    public static function exists($path)
    {
        return is_file(realpath($path));
    }

    /**
     * Remove the file
     *
     * @param string $path
     * @return bool
     */
    public static function remove($path)
    {
        if (!is_string($path) || trim($path) == '') {
            throw new Exception('File must be non-empty string');
        }

        $path = realpath($path);

        if (!is_file($path)) {
            return true;
        }

        return unlink($path);
    }

    /**
     * Checks if file is empty
     *
     * If trim set to true, file content trimmed before check
     *
     * @param string $path
     * @prarm bool $trim
     * @return bool
     */
    public static function isEmpty($path, $trim = false)
    {
        $path = realpath($path);

        if (!is_file($path)) {
            throw new Exception("File '{$path}' not found");
        }

        $filesize = self::size($path);

        if ($filesize === 0) {
            return true;
        }
        elseif (!$trim) {
            return false;
        }

        if (!is_readable($path)) {
            throw new Exception("File '{$path}' is not readable");
        }

        $empty = true;
        $handle = fopen($filename, "rb");

        while (!feof($handle)) {
            $chunk = fread($handle, 8192);
            if (!is_string($chunk)) {
                throw new Exception("Error while reading file '{$path}'");
            }
            if (trim($chunk)) {
                $empty = false;
                break;
            }
        }
        fclose($handle);

        return $empty;
    }

    /**
     * Get file content
     *
     * @param string $path
     * @return string
     */
    public static function read($path, $len = null, $offset = -1)
    {
        $path = Akt_Helper_Filesystem_Path::clean($path);

        if (!is_file($path) || !is_readable($path)) {
            throw new Exception("File '{$path}' not found or could not be read");
        }

        $content = is_int($len)
            ? @file_get_contents($path, true, null, $offset, $len)
            : @file_get_contents($path, true, null, $offset);

        return $content;
    }

    /**
     * Write data to the file in specified mode
     *
     * @param string $path
     * @param string $data
     * @param string $mode
     * @return bool
     */
    public static function write($path, $data, $mode = 'w')
    {
        $path = Akt_Helper_Filesystem_Path::clean($path);

        if (($handle = fopen($path, $mode)) === false) {
            return false;
        }

        if (fwrite($handle, $data) === false) {
            return false;
        }

        if (fclose($handle) === false) {
            return false;
        }

        return true;
    }

    /**
     * Append data to the file
     *
     * @param string $path
     * @param string $data
     * @return bool
     */
    public static function append($path, $data)
    {
        return self::write($path, $data, 'a');
    }

    /**
     * Get file size in bytes
     *
     * @param string $path
     * @return int
     */
    public static function size($path)
    {
        $path = Akt_Helper_Filesystem_Path::clean($path);

        if (!is_file($path)) {
            throw new Exception("File '{$path}' not found");
        }

        $filesize = @filesize($path);

        if ($filesize === false) {
            throw new Exception("Couldn't get file size");
        }

        return sprintf("%u", $filesize);
    }

    /**
     * Get file extension
     *
     * @param string $path
     * @return string
     */
    public static function extension($path)
    {
        return @pathinfo(Akt_Helper_Filesystem_Path::clean($path), PATHINFO_EXTENSION);
    }

    /**
     * Get file last access time in unix timestamp
     *
     * @param string $path
     * @return int
     */
    public static function lastAccess($path)
    {
        return @fileatime(Akt_Helper_Filesystem_Path::clean($path));
    }

    /**
     * Get file last modified time in unix timestamp
     *
     * @param string $path
     * @return int
     */
    public static function lastModified($path)
    {
        return @filemtime(Akt_Helper_Filesystem_Path::clean($path));
    }
}