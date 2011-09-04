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
     * @const List modes
     */
    const DIRS_AND_FILES = 0;
    const FILES_ONLY     = 1;
    const DIRS_ONLY      = 2;
    
    /**
     * @const List options
     */
    const RELATIVE = 1;
    const EXISTING = 2;
    
    /**
     * List's mode
     * @var int
     */
    protected $_mode = self::DIRS_AND_FILES;

    /**
     * List's base dir
     * @var string
     */
    protected $_basedir = self::PARENT;

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
     * @var Akt_Options 
     */
    protected $_options;
    
    /**
     * 
     * @var Akt_Options
     */
    protected $_customOptions;


    /**
     * Constructor
     *
     * @param int|string|array $basedirOrInclude
     * @param array|null $includeOrExclude
     * @param array|null $excludeOrOptions
     * @param array|int $options
     */
    public function __construct($basedirOrInclude = self::PARENT, 
        $includeOrExclude = null, $excludeOrOptions = null, $options = null
    ) {
        if (is_int($basedirOrInclude) || is_string($basedirOrInclude) || $basedirOrInclude === null) 
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
            throw new Akt_Exception("Bad parameters");
        }
        
        if ($basedir === null) {
            $basedir = self::ROOT;
        }
        elseif (!$basedir && $basedir !== '0') {
            $basedir = self::PARENT;
        }
        
        $this->_basedir = $basedir;
        
        if (is_int($include) || ($include instanceof Akt_Options)) {
            $options = $include;
            $include = null;
        }
        elseif (is_int($exclude) || ($exclude instanceof Akt_Options)) {
            $options = $exclude;
            $exclude = null;
        }
        
        if ($options instanceof Akt_Options) {
            $this->_options = $options;
        }
        else 
        {
            if (is_int($options)) 
            {
                $flags = $options;
                $options = array();
                $flagmap = array(
                    'relative' => self::RELATIVE,
                    'existing' => self::EXISTING,
                );
                foreach ($flagmap as $flagKey => $flagValue) {
                    if (($flags & $flagValue) == $flagValue) {
                        $options[$flagKey] = true;
                    }
                }
            }
            elseif (!is_array($options)) {
                $options = array();
            }
            $this->setOptions(new Akt_Options($options));
        }
        
        if (is_array($include)) {
            $this->addInclude($include);
        }
        if (is_array($exclude)) {
            $this->addExclude($exclude);
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
        if ($this->_customOptions instanceof Akt_Options) {
            $oldOptions = $this->_options;
            $this->_options = $this->_customOptions;
        }
        
        try {
            list($include, $exclude) = $this->getIncludeExclude();
            
            $it = $this->_createAppendIterator($include, $exclude);
    
            $it = new Akt_Filesystem_Iterator_Filter_CallbackFilterIterator($it, array(
                array(new Akt_Filesystem_Filter_Accept_UniqueFilesFilter(), 'accept')
            ));
            
            $relative = $this->getRelativeArray();
            
            if ($relative === null && $this->_isIncludeStatic($include)) {
                $relative = array(true);
            }
            
            if ($relative) {
                $it = new Akt_Filesystem_Iterator_FormatIterator($it, array(
                    new Akt_Filesystem_Filter_Format_RelativeFilter($relative, $this->getCwd())
                ));
            }
        }
        catch (Exception $e) {
            if (isset($oldOptions)) {
                $this->_options = $oldOptions;
                $this->_customOptions = null;
            }
            throw $e;
        }
        
        if (isset($oldOptions)) {
            $this->_options = $oldOptions;
            $this->_customOptions = null;
        }
        
        return $it;
    }
    
    /**
     * Create list's iterator
     * 
     * @throws Akt_Exception
     * @param  array $include
     * @param  array $exclude
     * @return Iterator
     */
    protected function _createAppendIterator($include, $exclude)
    {
        $appendIterator = new Akt_Filesystem_Iterator_Append_AppendIterator();
        
        $staticGlobs   = array();
        $globIterators = array();
        
        foreach ($include as $entry) 
        {
            if (!$entry instanceof Akt_Filesystem_Filter_Accept_Pathname_Glob) {
                throw new Akt_Exception("Include list can contain only glob filters");
            }
            /** @var $entry Akt_Filesystem_Filter_Accept_Pathname_Glob */
            if (Akt_Filesystem_Path_Glob::needScan($entry->getPattern())) {
                if (count($staticGlobs)) {
                    $globIterators[] = $this->_createGlobStaticIterator($staticGlobs);
                    $staticGlobs = array();
                }
                $globIterators[] = new Akt_Filesystem_Iterator_Filter_CallbackFilterIterator(
                    $this->_createGlobRecursiveDirectoryIterator($entry),
                    array(array($entry, 'accept'))
                );
            }
            else {
                $staticGlobs[] = $entry;
            }
        }
        if (count($staticGlobs)) {
            $globIterators[] = $this->_createGlobStaticIterator($staticGlobs);
        }
        
        foreach ($globIterators as $iterator) {
            $iterator = new Akt_Filesystem_Iterator_Filter_ExcludeFilterIterator($iterator, $exclude);
            $appendIterator->appendTraversable($iterator);
        }

        return $appendIterator;
    }
    
    /**
     * Create iterator for static glob pattern(s)
     * 
     * @param  array|Akt_Filesystem_Filter_Filename_Glob $glob
     * @return Iterator
     */
    protected function _createGlobStaticIterator($glob)
    {
        $it = new Akt_Filesystem_Iterator_Glob_GlobStaticIterator(
            $glob,
            Akt_Filesystem_Iterator_Glob_GlobStaticIterator::CURRENT_AS_PATHNAME
        );
        
        if ($this->options()->get('existing', false)) {
            if ($this->_mode == self::FILES_ONLY) {
                $it = new Akt_Filesystem_Iterator_Filter_FilesOnlyFilterIterator($it);
            }
            elseif ($this->_mode == self::DIRS_ONLY) {
                $it = new Akt_Filesystem_Iterator_Filter_DirectoriesOnlyFilterIterator($it);
            }
        }
        
        return $it;
    }

    /*
    $tree['/']['home']['user'][] = 'README';
    $tree['/']['home']['user']['lib'][] = '.gitignore';
    
    $tree => array(
        '/' => array(
            'home' => array(
                'user' => array(
                    'README',
                    'lib' => array(
                        '.gitignore'
                    )
                )
            )
        ),
        'c:/' => array(
            // ...
        ),
        '\\server\' => array(
            // ...
        ),
        'ssh2.sftp://' => array(
            'Resource#37' => array(
                'home' => array(
                    // ...
                )
            )
        )
    );
    */
    
    /**
     * Create iterator for magic glob pattern
     * 
     * @param  string|Akt_Filesystem_Filter_Filename_Glob $glob
     * @param  Akt_Filesystem_Cache_DirectoryTreeCache $directoryTreeCache
     * @return Iterator
     */
    protected function _createGlobRecursiveDirectoryIterator($glob, $directoryTreeCache = null)
    {
        $flags = Akt_Filesystem_Iterator_RecursiveDirectoryIterator::SKIP_DOTS
            | Akt_Filesystem_Iterator_RecursiveDirectoryIterator::CURRENT_AS_PATHNAME;

        $it = new RecursiveIteratorIterator(
            new Akt_Filesystem_Iterator_Glob_GlobRecursiveDirectoryIterator($glob, $flags),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        if ($this->_mode == self::FILES_ONLY) {
            $it = new Akt_Filesystem_Iterator_Filter_FilesOnlyFilterIterator($it);
        }
        elseif ($this->_mode == self::DIRS_ONLY) {
            $it = new Akt_Filesystem_Iterator_Filter_DirectoriesOnlyFilterIterator($it);
        }
        
        return $it;
    }

    /**
     * Get all include/exclude filters as flatten arrays
     * 
     * @return array
     */
    public function getIncludeExclude()
    {
        list($include, $exclude) = $this->_parseListIncludeExclude($this);
        
        if (!$include) {
            $include = array(array(array($this->getBasedir()), '**/*'));
        }
        
        $include = $this->_normalizeIncludeExclude($include);
        $exclude = $this->_normalizeIncludeExclude($exclude);

        return array($include, $exclude);
    }

    /**
     * Parse (recursively) list's nested include/exclude filters 
     * 
     * Return as flatten arrays
     * 
     * @param  Akt_Filesystem_List_AbstractList $list
     * @param  array $basedirStack
     * @return array
     */
    protected function _parseListIncludeExclude($list, $basedirStack = array())
    {
        $includeList = array();
        $excludeList = array();
        
        $basedirStack[] = $list->getBasedir();
        
        foreach ($list->getExclude() as $filter) {
            $excludeList[] = array($basedirStack, $filter);
        }
        
        foreach ($list->getInclude() as $filter) 
        {
            if ($filter instanceof self) {
                /** @var $filter Akt_Filesystem_List_AbstractList */
                list($incl, $excl) = $this->_parseListIncludeExclude($filter, $basedirStack);
                if (is_array($incl)) {
                    $includeList = array_merge($includeList, $incl);
                }
                if (is_array($excl)) {
                    $excludeList = array_merge($excludeList, $excl);
                }
            }
            else {
                $includeList[] = array($basedirStack, $filter);
            }
        }
        
        return array($includeList, $excludeList);
    }

    /**
     * Prepare include/exclude filters for iterators creation
     * 
     * @param  array $filterList
     * @return array
     */
    protected function _normalizeIncludeExclude($filterList)
    {
        if (!is_array($filterList)) {
            return array();
        }
        
        $newFilterList = array();

        foreach ($filterList as $entry) 
        {
            list($basedirStack, $filter) = $entry;
            
            if (is_string($filter)) {
                $filter = new Akt_Filesystem_Filter_Accept_Pathname_Glob($filter);
            }
            
            if ($filter instanceof Akt_Filesystem_Filter_Accept_Pathname_Glob)
            {
                $basedir  = $this->_getIncludeExcludeBasedir($basedirStack);
                $expanded = Akt_Filesystem_Path_Glob::expand($filter->getPattern());
                
                foreach ($expanded as $pattern) 
                {
                    if ((Akt_Filesystem_Path::isStreamWrapped($pattern)
                            || Akt_Filesystem_Path::isAbsoluteWin($pattern))
                        && $basedir !== null
                    ) {
                        throw new Akt_Exception("FileList's pattern path can be absolute only with ROOT basedir");
                    }
                    $pattern = ltrim($pattern, '/\\');
                    $newFilterList[] = new Akt_Filesystem_Filter_Accept_Pathname_Glob($pattern, $basedir);
                }
            }
        }

        return $newFilterList;
    }
    
    /**
     * Get basedir for include/exclude entry depends on stack
     * 
     * @param  array $basedirStack
     * @return null|string
     */
    protected function _getIncludeExcludeBasedir($basedirStack)
    {
        if (!is_array($basedirStack)) {
            return null;
        }

        $pathStack = array();
        foreach ($basedirStack as $entry) 
        {
            if (($entry === self::PARENT || $entry === false) 
                && is_array($pathStack) && !count($pathStack)
            ) {
                // top-level entry is parent, so using cwd as default
                $pathStack[] = $this->getCwd();
            }
            elseif ($entry === self::ROOT || $entry === null) {
                $pathStack = null;
            }
            elseif ($entry === self::CWD) {
                $pathStack = array($this->getCwd());
            }
            elseif (is_string($entry)) 
            {
                if (Akt_Filesystem_Path::isAbsolute($entry, true)) {
                    $pathStack = array($entry);
                }
                else {
                    if (!is_array($pathStack)) {
                        $pathStack = array();
                    }
                    elseif (!count($pathStack)) {
                        $pathStack = array($this->getCwd());
                    }
                    $pathStack[] = $entry;
                }
            }
        }

        if (!is_array($pathStack) || !count($pathStack)) {
            return null;
        }

        if (count($pathStack) == 1) {
            return $pathStack[0];
        }

        $dirsep = Akt_Filesystem_Path::getDirectorySeparator($pathStack[0]);
        return rtrim(implode($dirsep, $pathStack), '/\\');
    }
    
    /**
     * Check if all patterns in include list are static
     * 
     * @param  array $include
     * @return bool
     */
    protected function _isIncludeStatic($include)
    {
        foreach ($include as $filter) {
            /** @var $filter Akt_Filesystem_Filter_Accept_Pathname_Glob */
            if (Akt_Filesystem_Path_Glob::needScan($filter->getPattern())) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all files list
     *
     * @return array 
     */
    public function toArray()
    {
        $result = array();
        
        foreach ($this as $key => $entry) {
            $result[$key] = $entry;
        }
        
        return $result;
    }
    
    /**
     * Create and return new list filter
     *
     * @return Akt_Filesystem_List_ListFilter 
     */
    public function filter()
    {
        //return new Akt_Filesystem_List_ListFilter();
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
     * @param string|array|Akt_Filesystem_Filter_Accept_Pathname_Glob $include
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function addInclude($include)
    {
        if (!is_array($include)) {
            $include = array($include);
        }
        
        foreach ($include as $entry)
        {
            if (is_string($entry) || ($entry instanceof Akt_Filesystem_Filter_Accept_Pathname_Glob) 
                || ($entry instanceof self)
            ) {
                if (!in_array($entry, $this->_include, true)) {
                    $this->_include[] = $entry;
                }
            }
            else {
                throw new Akt_Exception("Bad parameter");
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
     * @param  string|array|Akt_Filesystem_Filter_Accept_Pathname_Glob $exclude
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function addExclude($exclude)
    {
        if (!is_array($exclude)) {
            $exclude = array($exclude);
        }
        
        foreach ($exclude as $entry) 
        {
            if (is_string($entry) || ($entry instanceof Akt_Filesystem_Filter_Accept_Pathname_Glob)) {
                if (!in_array($entry, $this->_exclude, true)) {
                    $this->_exclude[] = $entry;
                }
            }
            else {
                throw new Akt_Exception("Bad parameter");
            }
        }
        
        return $this;
    }

    /**
     * Set exclude patterns
     *
     * @param  array $exclude
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function setExclude($exclude)
    {
        $this->_exclude = array();
        $this->addExclude($exclude);
        return $this;
    }

    /**
     * Get options adapter
     *
     * @return Akt_Options
     */
    public function options()
    {
        if (!$this->_options instanceof Akt_Options) {
            $this->_options = new Akt_Options();
        }
        return $this->_options;
    }
    
    /**
     * Set options adapter
     *
     * @param  Akt_Options $options
     * @param  bool $clone
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function setOptions($options, $clone = false)     
    {
        if (!$options instanceof Akt_Options) {
            throw new Akt_Exception('Options must be an instance of Akt_Options');
        }
        $this->_options = $clone ? (clone $options) : $options;
        return $this;
    }
    
    /**
     * Set custom options for iterator
     * 
     * @param  array|Akt_Options $options
     * @param  bool $override
     * @return Akt_Filesystem_List_AbstractList
     */
    public function withOptions($options, $override = true)
    {
        $customOptions = $override ? (clone $this->options()) : (new Akt_Options());
        $this->_customOptions = $customOptions->merge($options);
        return $this;
    }
    
    /**
     * Get current custom options
     * 
     * @return Akt_Options
     */
    public function getCustomOptions()
    {
        return $this->_customOptions;
    }
    
    /**
     * Clear custom options
     * 
     * @return Akt_Filesystem_List_AbstractList
     */
    public function clearCustomOptions()
    {
        $this->_customOptions = null;
        return $this;
    }

    /**
     * @param  bool|string|array $path
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function relative($path = true)
    {
        $relative = $this->options()->get('relative');
        
        if ($path === null || $path === false) {
            if ($relative !== $path) {
                $this->options()->set('relative', $path);
            }
            return $this;
        }

        if ($relative === true) {
            $relative = array(true);
        }
        elseif (!is_array($relative)) {
            $relative = array();
        }
        
        if (!is_array($path)) {
            $path = array($path);
        }
        
        foreach ($path as $item) 
        {
            if (is_string($item)) {
                $item = rtrim(Akt_Filesystem_Path::clean($item), '/\\');
            }
            elseif ($item !== true) {
                continue;
            }
            
            if (!in_array($item, $relative)) {
                $relative[] = $item;
            }
        }
        
        $this->options()->set('relative', $relative);
        
        return $this;
    }
    
    /**
     * @param  bool|string|array $value
     * @return Akt_Filesystem_List_AbstractList
     */
    public function setRelative($value)
    {
        $this->clearRelative()->relative($value);
        return $this;
    }
    
    /**
     * Get relative paths as array
     * 
     * @return array|null|false
     */
    public function getRelativeArray()
    {
        $relative = $this->options()->get('relative');
        
        if ($relative === null || $relative === false) {
            return $relative;
        }
        
        if (!is_array($relative)) {
            $relative = array($relative);
        }
        
        return $relative;
    }
    
    /**
     * @return Akt_Filesystem_List_AbstractList
     */
    public function clearRelative()
    {
        $this->options()->set('relative', null);
        return $this;
    }

    /**
     * @return bool
     */
    public function isRelative()
    {
        $relative = $this->options()->get('relative');
        return $relative !== null && $relative !== false;
    }
    
    /**
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function absolute()
    {
        $this->relative(false);
        return $this;
    }
    
    /**
     * @param  bool $value
     * @return Akt_Filesystem_List_AbstractList 
     */
    public function existing($value = true)
    {
        $this->options()->set('existing', (bool) $value);
        return $this;
    }

    /**
     * Get current working dir
     * 
     * @return string
     */
    public function getCwd()
    {
        $cwd = $this->options()->get('cwd');
        if (!is_string($cwd)) {
            $cwd = getcwd();
        }
        return is_string($cwd) ? trim($cwd, '/\\') : '';
    }
    
    /**
     * Set working dir
     * 
     * @param  string $cwd
     * @return Akt_Filesystem_List_AbstractList
     */
    public function setCwd($cwd)
    {
        $this->options()->set('cwd', $cwd);
        return $this;
    }
}