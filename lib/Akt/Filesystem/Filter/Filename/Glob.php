<?php

/**
 * 
 */
class Akt_Filesystem_Filter_Filename_Glob 
    extends Akt_Filesystem_Filter_Filename_Pattern
{
    /**
     * Check if file should be kept
     *
     * @param SplFileInfo $fileinfo
     * @return bool 
     */    
    public function accept($fileinfo)
    {
        $subpath = Akt_Filesystem_Path::clean($this->getSubPathname($fileinfo), '/');
        return preg_match(self::toRegex($this->_pattern), $subpath) ? true : false;
    }

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
}