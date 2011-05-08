<?php

/**
 * 
 */
abstract class Akt_Filesystem_List_AbstractList implements IteratorAggregate
{
    /**
     * @const Basedir types
     */
    const PARENT = 0;
    const CWD    = 1;
    const ROOT   = 2;
    
    /**
     * @const List mode
     */
    const DIRS_AND_FILES = 0;
    const FILES_ONLY     = 1;
    const DIRS_ONLY      = 2;

    /**
     * List's base dir
     * @var string
     */
    protected $_basedir;
    
    /**
     * List's mode
     * @var int
     */
    protected $_mode = self::DIRS_AND_FILES;

    /**
     * Pathname include filter
     * @var array
     */
    protected $_include = array();
    
    /**
     * Pathname exclude filter
     * @var array
     */
    protected $_exclude = array();
    
    /**
     * List options
     * @var array 
     */
    protected $_options = array();


    /**
     * Constructor
     *
     * @param int|string|array $basedirOrInclude
     * @param array|null $includeOrExclude
     * @param array|null $excludeOrOptions
     * @param array|int $options
     */
    public function __construct($basedirOrInclude = self::PARENT, 
        $includeOrExclude = null, $excludeOrOptions = null, $options = null)
    {
        if (is_int($basedirOrInclude) || is_string($basedirOrInclude) 
            || $basedirOrInclude === null) 
        {
            $basedir = $basedirOrInclude;
            $include = $includeOrExclude;
            $exclude = $excludeOrOptions;
        }
        elseif (is_array($basedirOrInclude))
        {
            $basedir = self::PARENT;
            $include = $basedirOrInclude;
            $exclude = $includeOrExclude;
            $options = $excludeOrOptions;
        }
        else {
            throw new Akt_Exception("Bad parameter");
        }
        
        if ($basedir === null) {
            $basedir = self::ROOT;
        }
        elseif (!$basedir && $basedir !== '0') {
            $basedir = self::PARENT;
        }
        
        $this->_basedir = $basedir;
        
        if (is_int($include)) {
            $options = $include;
            $include = null;
        }
        elseif (is_int($exclude)) {
            $options = $exclude;
            $exclude = null;
        }
        
        /**
         * $options = array(
         *      'existing' => true|false,
         *      'relative' => true|false,
         * )
         */
        $this->_options = $options;
        
        if (is_array($include)) {
            $this->_include = array_merge($this->_include, $include);
        }
        
        if (is_array($exclude)) {
            $this->_exclude = array_merge($this->_exclude, $exclude);
        }
    }

    /**
     * Returns an Iterator for the current configuration.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @return Iterator
     */
    public function getIterator()
    {
        $iterator = new AppendIterator();
        
        $basedir = is_string($this->_basedir) ? $this->_basedir : getcwd();
        $iterator->append($this->_getDirIterator($basedir));
        
        return $iterator;
    }

    /**
     * Get iterators chain for specified directory
     *
     * @param string $dir
     * @return Iterator
     */
    protected function _getDirIterator($dir)
    {
        $flags = Akt_Filesystem_Iterator_RecursiveDirectoryIterator::SKIP_DOTS;

        $iterator = new RecursiveIteratorIterator(
            new Akt_Filesystem_Iterator_RecursiveDirectoryIterator($dir, $flags),
            RecursiveIteratorIterator::SELF_FIRST
        );

        if ($this->_mode == self::FILES_ONLY) {
            $iterator = new Akt_Filesystem_Filter_Iterator_FilesOnlyFilterIterator($iterator);
        }
        elseif ($this->_mode == self::DIRS_ONLY) {
            $iterator = new Akt_Filesystem_Filter_Iterator_DirectoriesOnlyFilterIterator($iterator);
        }

        if ($this->_include) 
        {
            $includeArray = array();
            
            foreach ($this->_include as $include) 
            {
                if (is_string($include)) {
                    $include = new Akt_Filesystem_Filter_Filename_Glob($include);
                }
                elseif (!$include instanceof Akt_Filesystem_Filter_FilterInterface) {
                    continue;
                }
                
                $includeArray[] = $include;
            }
            
            if ($includeArray) {
                $iterator = new Akt_Filesystem_Filter_Iterator_IncludeFilterIterator($iterator, $includeArray);
            }
        }

        if ($this->_exclude) 
        {
            $excludeArray = array();
            
            foreach ($this->_exclude as $exclude) 
            {
                if (is_string($exclude)) {
                    $exclude = new Akt_Filesystem_Filter_Filename_Glob($exclude);
                }
                elseif (!$exclude instanceof Akt_Filesystem_Filter_FilterInterface) {
                    continue;
                }
                
                $excludeArray[] = $exclude;
            }
            
            if ($excludeArray) {
                $iterator = new Akt_Filesystem_Filter_Iterator_ExcludeFilterIterator($iterator, $excludeArray);
            }
        }

        return $iterator;
    }
    
    /**
     * Get all files list
     *
     * @return array 
     */
    public function get()
    {
        $result = array();
        
        foreach ($this as $fileinfo) {
            $result[] = $fileinfo->getPathname();
        }
        
        return $result;
    }
    
    /**
     *
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function absolute()
    {
        // not implemented (by default)
        return $this;
    }
    
    /**
     *
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function relative()
    {
        // not implemented
        return $this;
    }
    
    /**
     *
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function existing()
    {
        // not implemented
        return $this;
    }

    /**
     * Create and return new list filter
     *
     * @return Akt_Filesystem_List_ListFilter 
     */
    public function filter()
    {
        return new Akt_Filesystem_List_ListFilter();
    }

    /**
     * Get current base dir
     *
     * @return int|string 
     */
    public function getBasedir()
    {
        return $this->_basedir;
    }

    /**
     * Set current base dir
     *
     * @param int|string|null $basedir
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function setBasedir($basedir)
    {
        $this->_basedir = $basedir;
        return $this;
    }

    /**
     * Get current mode
     *
     * @return int 
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Set list mode
     *
     * @param int $mode
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
        return $this;
    }

    /**
     * Get current include patterns
     *
     * @return array 
     */
    public function getInclude()
    {
        return $this->_include;
    }

    /**
     * Add include filter/pattern
     *
     * @param string|array|Akt_Filesystem_Filter_FilterInterface $include
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function addInclude($include)
    {
        if (!is_array($include)) {
            $include = array($include);
        }
        
        foreach ($include as $entry) {
            if (is_string($entry) 
                || ($entry instanceof Akt_Filesystem_Filter_FilterInterface))
            {
                if (!in_array($entry, $this->_include, true)) {
                    $this->_include[] = $entry;
                }
            }
            else {
                throw new Akt_Exception("Include filter must be a string"
                    . " or object that implements FilterInterface");
            }
        }
        
        return $this;
    }

    /**
     * Set include patterns
     *
     * @param array $include
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function setInclude($include)
    {
        $this->_include = array();
        $this->addInclude($include);
        return $this;
    }
    
    /**
     * Get current exclude patterns
     *
     * @return array 
     */
    public function getExclude()
    {
        return $this->_exclude;
    }

    /**
     * Add exclude filter/pattern
     *
     * @param string|array|Akt_Filesystem_Filter_FilterInterface $exclude
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function addExclude($exclude)
    {
        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }
        
        foreach ($exclude as $entry) {
            if (is_string($entry) 
                || ($entry instanceof Akt_Filesystem_Filter_FilterInterface))
            {
                if (!in_array($entry, $this->_exclude, true)) {
                    $this->_exclude[] = $entry;
                }
            }
            else {
                throw new Akt_Exception("Exclude filter must be a string"
                    . " or object that implements FilterInterface");
            }
        }
        
        return $this;
    }

    /**
     * Set exclude patterns
     *
     * @param array $exclude
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function setExclude($exclude)
    {
        $this->_exclude = array();
        $this->addExclude($exclude);
        return $this;
    }

    /**
     * Get current options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set options
     *
     * @param int|array $options
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
    }

}