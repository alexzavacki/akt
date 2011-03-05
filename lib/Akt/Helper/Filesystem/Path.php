<?php

/**
 * 
 */
class Akt_Helper_Filesystem_Path
{
    /**
     * Filesystem path cleaning
     *
     * Triming whitespaces
     * Removing double slashes
     * Converting all slashes to os-specific directory separators:
     *   '\' - for Windows
     *   '/' - for Unix-systems and Mac
     *
     * @param string $path
     * @return string
     */
    public static function clean($path)
    {
        $path = trim((string) $path);
        $path = preg_replace('#[/\\\\]+#', DIRECTORY_SEPARATOR, $path);
        return $path;
    }

    /**
     * PHP's realpath analog, but without file existence check
     * Transforms relative paths like a/b/./c/../d/ to absolute a/b/d/
     *
     * @param string $path
     * @return string
     */
    public static function realize($path)
    {
        $path = self::clean($path);
        $path = preg_replace('#\.{3,}' . preg_quote(DIRECTORY_SEPARATOR) . '#', '', $path);

        $drive = '';
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
        {
            if (preg_match('/([a-zA-Z]\:)(.*)/', $path, $matches)) {
                list($fullMatch, $drive, $path) = $matches;
            }
            else
            {
                $cwd = getcwd();
                $drive = substr($cwd, 0, 2);
                if (substr($path, 0, 1) != DIRECTORY_SEPARATOR) {
                    $path = substr($cwd, 3) . DIRECTORY_SEPARATOR . $path;
                }
            }
        }
        elseif (substr($path, 0, 1) != DIRECTORY_SEPARATOR) {
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        $stack = array();
        $parts = explode(DIRECTORY_SEPARATOR, $path);

        foreach ($parts as $dir)
        {
            if (strlen($dir) && $dir !== '.')
            {
                if ($dir == '..') {
                    array_pop($stack);
                }
                else {
                    array_push($stack, $dir);
                }
            }
        }

        return $drive . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $stack);
    }

    /**
     * Slashes the path
     *
     * Left, right or both
     *
     * @param string $path
     * @param bool $right
     * @param bool $left
     * @return string
     */
    public static function slash($path, $right = true, $left = false)
    {
        $path = self::clean($path);

        if ($path == '') {
            return $path;
        }

        if ($right === true) {
            if ($path[ strlen($path) - 1 ] != DIRECTORY_SEPARATOR) {
                $path .= DIRECTORY_SEPARATOR;
            }
        }

        if ($left === true) {
            if ($path[0] != DIRECTORY_SEPARATOR) {
                $path = DIRECTORY_SEPARATOR . $path;
            }
        }

        return $path;
    }

    /**
     * Unslashes the path
     *
     * Right or both
     *
     * @param string $path
     * @param bool $left
     * @return string
     */
    public static function unslash($path, $left = false)
    {
        $path = self::clean($path);

        if ($path == '') {
            return $path;
        }

        return ($left === false)
            ? rtrim($path, DIRECTORY_SEPARATOR)
            : trim($path, DIRECTORY_SEPARATOR);
    }

}