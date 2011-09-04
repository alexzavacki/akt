<?php

/**
 * 
 */
class Akt_Filesystem_Path_Glob
{
    /**
     * Automatically trim spaces around file names in braces
     * @var bool
     */
    protected static $_autoTrimInBraces = true;
    
    
    /**
     * Convert glob pattern to regex
     * 
     * @author Fabien Potencier <fabien@symfony.com> PHP port
     * @author Richard Clamp <richardc@unixbeard.net> Perl version
     *
     * @param  string $glob
     * @return string
     */
    public static function toRegex($glob)
    {
        $regex = '';
        $escaping = false;
        $inCurlies = 0;
        
        $glob = trim($glob, '/');
        $sizeGlob = strlen($glob);
        
        for ($i = 0; $i < $sizeGlob; $i++) 
        {
            $car = $glob[$i];

            if ('.' === $car || '(' === $car || ')' === $car || '|' === $car 
                || '+' === $car || '^' === $car || '$' === $car) 
            {
                $regex .= "\\$car";
            }
            elseif ('*' === $car) {
                if ($escaping) {
                    $regex .= '\\*';
                }
                else {
                    if ($i + 1 < $sizeGlob && $glob[$i + 1] == '*') {
                        $regex .= '.*';
                        $i++;
                        while ($i + 1 < $sizeGlob && $glob[$i + 1] == '/') {
                            $i++;
                        }
                    }
                    else {
                        $regex .= '[^/]*';
                    }
                }
            }
            elseif ('?' === $car) {
                $regex .= $escaping ? '\\?' : '[^/]';
            }
            elseif ('{' === $car) {
                $regex .= $escaping ? '\\{' : '(';
                if (!$escaping) {
                    ++$inCurlies;
                }
            }
            elseif ('}' === $car && $inCurlies) {
                $regex .= $escaping ? '}' : ')';
                if (!$escaping) {
                    --$inCurlies;
                }
            }
            elseif (',' === $car && $inCurlies) {
                $regex .= $escaping ? ',' : '|';
            }
            elseif ('\\' === $car) {
                if ($escaping) {
                    $regex .= '\\\\';
                    $escaping = false;
                }
                else {
                    $escaping = true;
                }

                continue;
            }
            else {
                $regex .= $car;
            }
            $escaping = false;
        }

        return '#^' . $regex . '$#';
    }

    /**
     * Check if glob pattern needs dir scan
     * 
     * @static
     * @throws Akt_Exception
     * @param  string $glob
     * @return bool
     */
    public static function needScan($glob)
    {
        if (!is_string($glob)) {
            throw new Akt_Exception("Bad parameter");
        }
        return strpos($glob, '*') !== false;
    }
    
    /**
     * Expand glob pattern
     * 
     * @static
     * @throws Akt_Exception
     * @param  string $glob
     * @return array
     */
    public static function expand($glob)
    {
        return self::expandBraces($glob);
    }
    
    /**
     * Expand glob braces
     * 
     * Accepts any nesting level
     * 
     * @static
     * @throws Akt_Exception
     * 
     * @param  string $glob
     * @param  bool $trim
     * @return array
     */
    public static function expandBraces($glob, $trim = null)
    {
        if (!is_string($glob)) {
            throw new Akt_Exception("Glob pattern must be a string");
        }

        if (strpos($glob, '{') === false && strpos($glob, '}') === false) {
            return array($glob);
        }
        
        if (!self::hasCorrectBraces($glob)) {
            throw new Akt_Exception("Glob pattern has incorrect braces placement");
        }

        $result = array();
        
        $start    = null;
        $end      = null;
        $globSize = strlen($glob);
        $inBraces = 0;
        
        for ($curpos = 0; $curpos < $globSize; $curpos++) {
            if ($glob[$curpos] == '{') {
                if ($start === null) {
                    $start = $curpos;
                }
                ++$inBraces;
            }
            elseif ($glob[$curpos] == '}') {
                --$inBraces;
                if ($inBraces === 0 && $start !== null) {
                    $end = $curpos;
                    break;
                }
            }
        }
        
        if ($start === null || $end === null) {
            throw new Akt_Exception("Bad glob pattern");
        }
        
        $left  = $start > 0 ? substr($glob, 0, $start) : '';
        $right = $end < $globSize - 1 ? substr($glob, $end + 1) : '';
        
        $inBraces   = 0;
        $substring  = null;
        $substrings = array();
        
        for ($curpos = $start + 1, $from = $curpos; $curpos < $end; $curpos++) 
        {
            if ($glob[$curpos] == '{') {
                ++$inBraces;
            }
            elseif ($glob[$curpos] == '}') {
                --$inBraces;
            }
            
            if ($inBraces !== 0) {
                continue;
            }
            
            if ($glob[$curpos] == ',') 
            {
                $substrings[] = substr($glob, $from, $curpos - $from);
                $from = $curpos + 1;
                
                if ($curpos == $end - 1) {
                    $substrings[] = '';
                }
            }
            elseif ($curpos == $end - 1 && $curpos >= $from) {
                $substrings[] = substr($glob, $from, $curpos - $from + 1);
                $from = $curpos + 1;
            }
        }

        foreach ($substrings as $substring) {
            if ((self::$_autoTrimInBraces === true && $trim === null) || $trim === true) {
                $substring = trim($substring);
            }
            $result = array_merge(
                $result, 
                self::expandBraces(sprintf("%s%s%s", $left, $substring, $right))
            );
        }

        return $result;
    }
    
    /**
     * Check that glob pattern has equal number of opening and closing braces
     * 
     * @static
     * @param  string $glob
     * @return bool
     */
    public static function hasCorrectBraces($glob)
    {
        $inBraces = 0;
        
        for ($i = 0, $globSize = strlen($glob); $i < $globSize; $i++) {
            if ($glob[$i] === '{') {
                ++$inBraces;
            }
            elseif ($glob[$i] === '}') {
                --$inBraces;
            }
            if ($inBraces < 0) {
                return false;
            }
        }
        
        return $inBraces == 0;
    }

    /**
     * Set auto trimming spaces in braces
     * 
     * @param  boolean $autoTrim
     * @return void
     */
    public static function setAutoTrimInBraces($autoTrim)
    {
        self::$_autoTrimInBraces = (bool) $autoTrim;
    }

    /**
     * Get auto trimming spaces option
     * 
     * @return boolean
     */
    public static function getAutoTrimInBraces()
    {
        return self::$_autoTrimInBraces;
    }
}