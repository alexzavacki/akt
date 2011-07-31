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
     * @todo On unix it is possible to create directory with backslash char in name, e.g.:
     *       /home/user/path/fol\der/file,
     *       but self::clean() unifies path by converting all slash-chars to specified separator,
     *       so this path will be converted to:
     *       /home/user/path/fol/der/file,
     *       and they are not same
     * 
     * @param string $path
     * @param string|int|null $separator
     * @return string
     */
    public static function clean($path, $separator = null)
    {
        $path = trim($path);
        $dirsep = self::getDirectorySeparator($path, $separator);
        
        if (self::isStreamWrapped($path)) {
            $scheme = self::getStreamWrapperScheme($path);
            $path = $scheme . '://'
                . ltrim(strstr(preg_replace('#[/\\\\]+#', '/', $path), ':/'), ':/');
        }
        elseif (self::isUnc($path)) {
            $path = str_repeat($dirsep, 2) 
                . ltrim(preg_replace('#[/\\\\]+#', $dirsep, $path), $dirsep);
        }
        else {
            $path = preg_replace('#[/\\\\]+#', $dirsep, $path);
        }
        
        return $path;
    }

    /**
     * PHP's realpath analog, but without file existence check
     * Transforms relative paths like a/b/./c/../d/ to absolute a/b/d/
     *
     * @param string $path
     * @param string|int|null $separator
     * @param bool $expandSymlink
     * @return string|false
     */
    public static function realize($path, $separator = null, $expandSymlink = false)
    {
        $path = preg_replace('#\.{3,}#', '', $path);
        $path = self::clean($path, $separator);

        $dirsep = self::getDirectorySeparator($path, $separator);

        $prefix = '';
        if (self::isStreamWrapped($path)) {
            $scheme = self::getStreamWrapperScheme($path);
            $prefix = $scheme . '://';
            $path = ltrim(strstr($path, ':/'), ':/');
            if ($path) {
                $path = explode($dirsep, $path);
                $prefix .= array_shift($path) . $dirsep;
            }
        }
        elseif (self::isUnc($path)) {
            $path = explode($dirsep, ltrim($path, $dirsep));
            $prefix = str_repeat($dirsep, 2) . array_shift($path) . $dirsep;
        }
        else {
            if (self::isAbsoluteWin($path)) {
                $path = explode($dirsep, $path);
                $prefix = array_shift($path) . $dirsep;
            }
            elseif (self::isAbsoluteUnix($path)) {
                $path = explode($dirsep, ltrim($path, $dirsep));
                $prefix = $dirsep;
            }
        }

        if (is_string($path)) {
            $path = explode($dirsep, trim($path, $dirsep));
        }
        elseif (!is_array($path)) {
            return false;
        }

        $stack = array();
        
        foreach ($path as $entry)
        {
            $entry = trim($entry);
            if ($entry && $entry != '.') {
                if ($entry == '..') {
                    array_pop($stack);
                }
                else {
                    array_push($stack, $entry);
                }
            }
        }
        
        $path = trim($prefix . implode($dirsep, $stack));

        if (strlen($path) && $expandSymlink && file_exists($path) && is_link($path)) {
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
     * @param string|int|null $separator
     * @return string
     */
    public static function slash($path, $left = false, $right = true, $separator = null)
    {
        if ($path == '') {
            return $path;
        }

        $dirsep = self::getDirectorySeparator($path, $separator);

        if ($left) {
            $path = $dirsep . ltrim($path, $dirsep);
        }
        if ($right) {
            $path = rtrim($path, $dirsep) . $dirsep;
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
     * @param string|int|null $separator
     * @return string
     */
    public static function unslash($path, $left = false, $right = true, $separator = null)
    {
        if ($path == '') {
            return $path;
        }

        $dirsep = self::getDirectorySeparator($path, $separator);

        if ($left) {
            $path = ltrim($path, $dirsep);
        }
        if ($right) {
            $path = rtrim($path, $dirsep);
        }

        return $path;
    }

    /**
     * Get directory separator depending on $path type
     *
     * 1. Stream wrapped paths - '/'
     * 2. UNC paths:
     *    If $separator equals to DIRSEP_UNIX - '/',
     *    else - '\'
     * 3. Local paths:
     *    If $separator equals to DIRSEP_UNIX - '/',
     *    else if $separator equals to DIRSEP_Win - '\',
     *    else if $separator is string it will be returned as separator
     * 4. In all other cases returns os-specific DIRECTORY_SEPARATOR constant
     *
     * @static
     * @param  string $path
     * @param  string|int|null $separator
     * @return null|string
     */
    public static function getDirectorySeparator($path, $separator = null)
    {
        if (self::isStreamWrapped($path)) {
            return '/';
        }

        if (self::isUnc($path)) {
            return $separator === self::DIRSEP_UNIX ? '/' : '\\';
        }

        if ($separator === self::DIRSEP_UNIX) {
            return '/';
        }
        elseif ($separator === self::DIRSEP_WIN) {
            return '\\';
        }
        elseif (is_string($separator)) {
            return $separator;
        }

        return DIRECTORY_SEPARATOR;
    }

    /**
     * Check if $path is absolute
     * 
     * Returns true if $path is absolute local or stream wrapped or UNC
     * 
     * Parameter $os is similar to the isAbsoluteLocal()'s os parameter,
     * and $strict param - to the isStreamWrapped()'s strict
     * 
     * By default absolute local will be checked for current local OS,
     * and stream wrapped with non-strict param
     * 
     * @param  string $path
     * @param  string|bool|null $os
     * @param  bool $strict
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
     * Check if $path is local and absolute
     * 
     * If $os is null or false, check for current OS (default)
     * If $os is 'any' or true, check for all known
     * If $os equals to 'win' or 'unix', then for the corresponding system 
     *
     * @param  string $path
     * @param  bool|string|null $os
     * @return bool 
     */
    public static function isAbsoluteLocal($path, $os = null)
    {
        if ($os !== true && $os != 'any') {
            if ($os === null || $os === false) {
                $os = PHP_OS;
            }
            $os = strstr(strtolower($os), 'win') ? 'win' : 'unix';
        }
        
        switch ($os)
        {
            default:
            case 'win':
                if (self::isAbsoluteWin($path)) {
                    return true;
                }
                if ($os === 'win') {
                    break;
                }
            
            case 'unix':
                if (self::isAbsoluteUnix($path)) {
                    return true;
                }
                if ($os === 'unix') {
                    break;
                }
        }
        
        return false;
    }

    /**
     * Check if $path is absolute local windows path
     * 
     * If $strict is true, path must contain volume drive letter,
     * else paths with first slash character will be also treated as absolute 
     * 
     * @static
     * @param  string $path
     * @param  bool $strict
     * @return bool
     */
    public static function isAbsoluteWin($path, $strict = true)
    {
        $path = strtr($path, '/\\', '\\\\');
        
        if (preg_match('#^[a-z]+?:(\\\\([^\\\\]|$)|$)#i', $path)) {
            return true;
        }
        if (!$strict && preg_match('#^\\\\([^\\\\]|$)#i', $path)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get strict absolute local windows path
     * 
     * If $path is strictly absolute, i.e. contains volume drive letter, 
     * then this path will be returned w/o any modification.
     * 
     * Else if path is absolute non-strictly, i.e. begins with slash,
     * then path will be defined relative to the cwd's volume drive.
     * 
     * If path is relative, then it will be appended to the cwd
     * 
     * If $cwd is not string or is not strict absolute, 
     * then cwd() function will be called to determine current working dir
     * 
     * @static
     * @param  $path
     * @param  string|null $cwd
     * @return string|bool
     */
    public static function getAbsoluteWinPath($path, $cwd = null)
    {
        if (!is_string($path)) {
            return false;
        }
        
        if (self::isAbsoluteWin($path, true)) {
            // Path is strict absolute
            return $path;
        }
        
        if (!is_string($cwd) || !self::isAbsoluteWin($cwd, true)) {
            // We need strict absolute cwd
            $cwd = getcwd();
            if (!self::isAbsoluteWin($cwd, true)) {
                return false;
            }
        }
        $cwd = strtr($cwd, '/\\', '\\\\');
        
        if (self::isAbsoluteWin($path, false)) {
            // Path is absolute, but not strict, get just drive letter from cwd
            if (($pos = strpos($cwd, '\\')) !== false) {
                $cwd = substr($cwd, 0, $pos);
            }
        }
        
        return rtrim($cwd, '\\') . '\\' . ltrim($path, '\\/');
    }

    /**
     * Check if $path is absolute local unix path
     *
     * @static
     * @param  string $path
     * @return bool
     */
    public static function isAbsoluteUnix($path)
    {
        return substr($path, 0, 1) == '/';
    }

    /**
     * Returns true if $path is correct UNC path
     *
     * @param  string $path
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
     * @param  string $path
     * @param  bool $strict
     * @return bool 
     */
    public static function isStreamWrapped($path, $strict = false)
    {
        $scheme = self::getStreamWrapperScheme($path);
        if (!$scheme) {
            return false;
        }
        if ($strict && !in_array($scheme, stream_get_wrappers())) {
            return false;
        }
        return true;
    }
    
    /**
     * Get stream wrapper protocol part
     *
     * @param  string $path 
     * @return string|false
     */
    public static function getStreamWrapperScheme($path)
    {
        if (!preg_match("#^([^:/]+?)://#", $path, $m)) {
            return false;
        }
        return strtolower($m[1]);
    }
}
