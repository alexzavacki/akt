<?php

/**
 * 
 */
class Akt_Filesystem_Path
{
    /**
     * @const Directory separator types
     */
    const DIRSEP_OS   = 0;
    const DIRSEP_UNIX = 1;
    const DIRSEP_WIN  = 2;
    
    
    /**
     * Filesystem path cleaning
     *
     * Triming whitespaces
     * Removing double slashes
     * 
     * Slashes converting depends on $separator and path type
     * 
     * 1. If $path is stream wrapped, all slash-chars converting to forward slashes '/'
     * 2. If $path is UNC-path, slash type depends on $separator:
     *      if $separator is DIRSEP_UNIX, slash type is forward slash, 
     *      in all other cases - backslash '\'
     * 3. Else $path is local and directory separator depends only on $separator:
     *      if $separator is integer and equals to one of DIRSEP constants 
     *          appropriate separator is using
     *      if $separator is string, this string is using as directory separator
     *      in all other cases os-specific DIRECTORY_SEPARATOR constant is using
     * 
     * @param string $path
     * @param string|int|null $separator
     * @return string
     */
    public static function clean($path, $separator = null)
    {
        $path = trim($path);
        
        if (self::isStreamWrapped($path)) {
            $protocol = self::getStreamWrapperProtocol($path);
            $path = $protocol . '://'
                . ltrim(strstr(preg_replace('#[/\\\\]+#', '/', $path), ':/'), ':/');
        }
        elseif (self::isUnc($path)) {
            $dirsep = $separator === self::DIRSEP_UNIX ? '/' : '\\';
            $path = str_repeat($dirsep, 2) 
                . ltrim(preg_replace('#[/\\\\]+#', $dirsep, $path), $dirsep);
        }
        else {
            if ($separator === self::DIRSEP_UNIX) {
                $dirsep = '/';
            }
            elseif ($separator === self::DIRSEP_WIN) {
                $dirsep = '\\';
            }
            elseif (is_string($separator)) {
                $dirsep = $separator;
            }
            else {
                $dirsep = DIRECTORY_SEPARATOR;
            }
            $path = preg_replace('#[/\\\\]+#', $dirsep, $path);
        }
        
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
        
        $path = $drive . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $stack);
        
        if (file_exists($path) && is_link($path)) {
            $path = readlink($path);
        }
        
        return $path;
    }

    /**
     * Slash the path
     *
     * Left, right or both
     * By default slashes only right side
     *
     * @param string $path
     * @param bool $left
     * @param bool $right
     * @return string
     */
    public static function slash($path, $left = false, $right = true)
    {
        $path = self::clean($path);

        if ($path == '') {
            return $path;
        }

        if ($left) {
            $path = DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
        }
        if ($right) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    /**
     * Unslash the path
     *
     * Left, right or both
     * By default unslashes only right side
     *
     * @param string $path
     * @param bool $left
     * @param bool $right
     * @return string
     */
    public static function unslash($path, $left = false, $right = true)
    {
        $path = self::clean($path);

        if ($path == '') {
            return $path;
        }

        if ($left) {
            $path = ltrim($path, DIRECTORY_SEPARATOR);
        }
        if ($right) {
            $path = rtrim($path, DIRECTORY_SEPARATOR);
        }

        return $path;
    }
    
    /**
     * Check if $path is local and absolute
     * 
     * If $os is null, check for current OS
     * If $os is true or 'all', check for all OS
     *
     * @param string $path
     * @param string $os
     * @return bool 
     */
    public static function isAbsoluteLocal($path, $os = null)
    {
        if ($os !== true && $os != 'all') {
            if ($os === null) {
                $os = PHP_OS;
            }
            $os = strstr(strtolower($os), 'win') ? 'win' : 'unix';
        }
        
        switch ($os)
        {
            default:
            case 'win':
                $pattern = '#^[a-z]+?:\\\\([^\\\\]|$)#i';
                if (preg_match($pattern, strtr($path, '/\\', '\\\\'))) {
                    return true;
                }
                if ($os == 'win') {
                    break;
                }
            
            case 'unix':
                if (substr($path, 0, 1) == '/') {
                    return true;
                }
                if ($os == 'unix') {
                    break;
                }
        }
        
        return false;
    }
    
    /**
     * Check if $path is absolute
     * 
     * Returns true if $path is absolute local or stream wrapped or UNC
     * 
     * If $os is null, check for current OS
     * If $os is true or 'all', check for all OS
     * 
     * If $strict is true stream wrapper must be registered
     *
     * @param string $path
     * @param string $os
     * @param bool $strict
     * @return bool 
     */    
    public static function isAbsolute($path, $os = null, $strict = false)
    {
        if (self::isStreamWrapped($path, $strict)) {
            return true;
        }
        elseif (self::isUnc($path)) {
            return true;
        }
        elseif (self::isAbsoluteLocal($path, $os)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns true if $path is correct UNC path
     *
     * @param string $path
     * @return bool 
     */
    public static function isUnc($path)
    {
        return strlen($path) > 2 && substr($path, 0, 1) == substr($path, 1, 1)
            && preg_match('#^\\\\\\\\[^\\\\]+#i', strtr($path, '/\\', '\\\\'));
    }
    
    /**
     * Check if $path is "stream wrapped"
     * 
     * Returns true if $path contains wrapper's protocol part
     * 
     * If $strict is true stream wrapper must be registered
     *
     * @param string $path
     * @param bool $strict
     * @return bool 
     */
    public static function isStreamWrapped($path, $strict = false)
    {
        $protocol = self::getStreamWrapperProtocol($path);
        if (!$protocol) {
            return false;
        }
        if ($strict && !in_array($protocol, stream_get_wrappers())) {
            return false;
        }
        return true;
    }
    
    /**
     * Get stream wrapper protocol part
     *
     * @param string $path 
     * @return string|false
     */
    public function getStreamWrapperProtocol($path)
    {
        if (!preg_match("#^([^\:/]+?)\://#", $path, $m)) {
            return false;
        }
        return strtolower($m[1]);
    }
}