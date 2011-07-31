<?php

/**
 *
 */
class Akt_Filesystem_File
{
    /**
     * Create file
     *
     * @param string $path
     * @param int $chmod
     * @param int $dirchmod
     * @return bool
     */
    public static function create($path, $chmod = 0777, $dirchmod = null)
    {
        $path = Akt_Filesystem_Path::clean($path);

        if (self::exists($path)) {
            return true;
        }

        $dir = dirname($path);
        if (!Akt_Filesystem_Dir::exists($dir)) {
            if (!$dirchmod) {
                $dirchmod = $chmod;
            }
            Akt_Filesystem_Dir::create($dir, $dirchmod);
        }

        if (is_writable($dir) && touch($path)) {
            chmod($path, $chmod);
            return true;
        }

        return false;
    }

    /**
     * Check if file exists
     *
     * @param  string $path
     * @param  bool $useIncludePath
     * @return bool
     */
    public static function exists($path, $useIncludePath = false)
    {
        if (is_file($path)) {
            return true;
        }
        
        if ($useIncludePath) {
            foreach (explode(PATH_SEPARATOR, get_include_path()) as $subpath) {
                if (is_file($subpath . '/' . $path)) {
                    return true;
                }
            }
        }

        return false;
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
     * If $trim is true, file content will be trimmed before check
     *
     * @param  string $path
     * @param  bool $trim
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
        $handle = fopen($path, "rb");

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
     * @param int $len
     * @param int $offset
     * @return string
     */
    public static function read($path, $len = null, $offset = -1)
    {
        $path = Akt_Filesystem_Path::clean($path);

        if (!is_file($path) || !is_readable($path)) {
            throw new Exception("File '{$path}' not found or could not be read");
        }

        $content = $len !== null
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
     * @return int|false
     */
    public static function write($path, $data, $mode = 'w')
    {
        $path = Akt_Filesystem_Path::clean($path);
        $dir = dirname($path);
        
        if (!is_dir($dir)) {
            Akt_Filesystem_Dir::create($dir);
        }

        if (($handle = fopen($path, $mode)) === false) {
            return false;
        }
        
        $result = fwrite($handle, $data);
        fclose($handle);
        
        return $result;
    }

    /**
     * Append data to the file
     *
     * @param string $path
     * @param string $data
     * @return int|false
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
        $path = Akt_Filesystem_Path::clean($path);

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
        return @pathinfo(Akt_Filesystem_Path::clean($path), PATHINFO_EXTENSION);
    }

    /**
     * Get file last access time in unix timestamp
     *
     * @param string $path
     * @return int
     */
    public static function lastAccess($path)
    {
        return @fileatime(Akt_Filesystem_Path::clean($path));
    }

    /**
     * Get file last modified time in unix timestamp
     *
     * @param string $path
     * @return int
     */
    public static function lastModified($path)
    {
        return @filemtime(Akt_Filesystem_Path::clean($path));
    }
}
