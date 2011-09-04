<?php

/**
 * 
 */
class Akt_Filesystem_Filter_Format_RelativeFilter
    extends Akt_Filesystem_Filter_Format_AbstractFormatFilter
{
    /**
     * Relative basedirs
     * @var array
     */
    protected $_relatives = array();
    
    /**
     * Current working dir for list
     * @var string
     */
    protected $_cwd;
    
    
    /**
     * Constructor.
     * 
     * @param string|array $relatives
     * @param string $cwd
     */
    public function __construct($relatives = array(), $cwd = null)
    {
        if ($relatives) {
            $this->add($relatives);
        }
        if (is_string($cwd)) {
            $this->_cwd = $cwd;
        }
    }
    
    /**
     * Format iterator's current file name
     * 
     * @param  string|SplFileInfo $file
     * @return string
     */
    public function format($file)
    {
        $file = $this->getPathname($file);
        $dirsep = Akt_Filesystem_Path::getDirectorySeparator($file);
        $file = strtr($file, '/\\', str_repeat($dirsep, 2));
        
        if ($this->_relatives) 
        {
            foreach ($this->_relatives as $relative) 
            {
                $relatives = array();
                if ($relative === true) {
                    if (!is_string($this->_cwd)) {
                        continue;
                    }
                    $relatives[] = $this->_cwd;
                }
                elseif (is_string($relative)) {
                    $relatives[] = $relative;
                    if (!Akt_Filesystem_Path::isAbsolute($relative, 'any') && is_string($this->_cwd)) {
                        $relatives[] = rtrim($this->_cwd, '/\\') . "/" . ltrim($relative, '/\\');
                    }                    
                }
                else {
                    continue;
                }
                
                foreach ($relatives as $relative) {
                    $relative = rtrim(strtr($relative, '/\\', str_repeat($dirsep, 2)), '/\\');
                    if (strpos($file, $relative) === 0) {
                        $file = ltrim(substr($file, strlen($relative)), '/\\');
                        break;
                    }
                }
            }
        }
        
        return $file;
    }
    
    /**
     * Add relatives to list
     * 
     * @param  mixed $values
     * @return Akt_Filesystem_Filter_Format_RelativeFilter
     */
    public function add($values)
    {
        if (!is_array($values)) {
            $values = array($values);
        }
        
        foreach ($values as $value) {
            if (!in_array($value, $this->_relatives)) {
                $this->_relatives[] = $value;
            }
        }
        
        return $this;
    }

    /**
     * Set current working dir
     * 
     * @param  string $cwd
     * @return Akt_Filesystem_Filter_Format_RelativeFilter
     */
    public function setCwd($cwd)
    {
        $this->_cwd = $cwd;
        return $this;
    }

    /**
     * Get current working dir
     * 
     * @return string
     */
    public function getCwd()
    {
        return $this->_cwd;
    }
}