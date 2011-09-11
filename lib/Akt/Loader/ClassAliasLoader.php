<?php

/**
 * Akt_Loader_ClassAliasLoader
 */
class Akt_Loader_ClassAliasLoader extends Akt_Loader_AbstractLoader
{
    /**
     * User registered aliases
     * @var array
     */
    protected static $_aliases = array();

    /**
     * Default aliases
     * Will be initialized by first autoloader use
     * @var array
     */
    protected static $_defaultAliases;

    /**
     * Default aliases map
     * @var array 
     */
    protected static $_defaultAliasesMap = array(
        'Config'   => 'Akt_Config',
        'Registry' => 'Akt_Registry',
        'path' => 'Akt_Filesystem_Path',
        'dir'  => 'Akt_Filesystem_Dir',
        'file' => 'Akt_Filesystem_File',
        'DirList'  => 'Akt_Filesystem_List_DirList',
        'FileList' => 'Akt_Filesystem_List_FileList',
        'FilePack' => 'Akt_Filesystem_List_FilePack',
        'FileStream' => 'Akt_Filesystem_Stream_FileStream',
        'FileSync' => 'Akt_Filesystem_Sync_FileSync',
    );

    /**
     * Aliases prefix for class names conflict resolving
     * @var string
     */
    protected static $_defaultAliasesPrefix = '';

    /**
     * Registered namespaces
     * @var array 
     */
    protected static $_namespaces = array();

    /**
     * Is autoloader disabled?
     * @var bool
     */
    protected static $_disabled = false;


    /**
     * Loads Akt class or interface
     *
     * @param string $class
     * @return string|false
     */
    public static function load($class)
    {
        if (self::$_disabled) {
            return false;
        }

        if (class_exists($class, false)) {
            return true;
        }

        // @todo Move lowercase key search to Array Helper

        // try to load user registered alias
        if (!self::loadAliasClass($class) || !class_exists($class, false)) {
            // then namespace alias
            if (!self::loadNamespaceAliasClass($class) || !class_exists($class, false)) {
                // and last... default alias
                self::loadDefaultAliasClass($class);
            }
        }

        return class_exists($class, false) ? $class : false;
    }

    /**
     * Try to load user registered alias class
     * 
     * @static
     * @param  string $class
     * @return bool
     */
    public static function loadAliasClass($class)
    {
        if (!self::$_aliases) {
            return false;
        }

        $classLower = strtolower($class);

        $registeredAliasesLowerKeys = array_combine(
            array_keys(array_change_key_case(self::$_aliases, CASE_LOWER)),
            array_keys(self::$_aliases)
        );

        if (isset($registeredAliasesLowerKeys[$classLower])) {
            $classKey = $registeredAliasesLowerKeys[$classLower];
            if (isset(self::$_aliases[$classKey])) {
                classAlias(self::$_aliases[$classKey], $class);
                return class_exists($class, false);
            }
        }

        return false;
    }

    /**
     * Register alias

     * @param string|array $classOrAliases
     * @param string|null $aliasOrOverwrite
     * @param bool $overwrite
     * @return void
     */
    public static function registerAlias($classOrAliases, $aliasOrOverwrite = null,
        $overwrite = false
    ) {
        if (is_string($classOrAliases) && is_string($aliasOrOverwrite)) {
            $aliases = array($aliasOrOverwrite => $classOrAliases);
        }
        elseif (is_array($classOrAliases)) {
            $aliases = $classOrAliases;
            if (is_bool($aliasOrOverwrite)) {
                $overwrite = $aliasOrOverwrite;
            }
        }
        else {
            throw new Akt_Exception("Alias must be a string or array of pairs 'alias' => 'class'");
        }

        $registeredAliasesLowerKeys = array_combine(
            array_keys(array_change_key_case(self::$_aliases, CASE_LOWER)),
            array_keys(self::$_aliases)
        );

        foreach ($aliases as $classAlias => $className)
        {
            if (!is_string($className)) {
                throw new Akt_Exception('Class name must be a string');
            }

            $classAliasLower = strtolower($classAlias);

            if (isset($registeredAliasesLowerKeys[$classAliasLower]))
            {
                if (!$overwrite) {
                    continue;
                }
                
                $aliasKey = $registeredAliasesLowerKeys[$classAliasLower];
                if (isset(self::$_aliases[$aliasKey])) {
                    unset(self::$_aliases[$aliasKey]);
                    unset($registeredAliasesLowerKeys[$classAliasLower]);
                }
            }

            self::$_aliases[$classAlias] = $className;
            $registeredAliasesLowerKeys[$classAliasLower] = $classAlias;
        }
    }

    /**
     * Remove user registered alias(es)
     * 
     * @static
     * @throws Akt_Exception
     * @param string|array|null $alias
     * @return void
     */
    public static function removeAlias($alias = null)
    {
        if ($alias === null) {
            self::$_aliases = array();
            return;
        }

        if (!is_array(self::$_aliases) || !self::$_aliases) {
            self::$_aliases = array();
            return;
        }

        if (is_string($alias)) {
            $alias = array($alias);
        }
        elseif (!is_array($alias)) {
            throw new Akt_Exception('Alias name must be a string or array of strings');
        }

        self::_removeAliasesFromArray(self::$_aliases, $alias);
    }

    /**
     * Try to load default alias class
     *
     * @static
     * @param  string $class
     * @return bool
     */
    public static function loadDefaultAliasClass($class)
    {
        // Populate default aliases list if not initialized yet
        self::getDefaultAliases();

        if (!self::$_defaultAliases) {
            return false;
        }

        $classLower = strtolower($class);

        if (is_string(self::$_defaultAliasesPrefix) && strlen(self::$_defaultAliasesPrefix)) {
            if (strpos($classLower, strtolower(self::$_defaultAliasesPrefix)) !== 0) {
                return false;
            }
            $classLower = substr($classLower, strlen(self::$_defaultAliasesPrefix));
        }

        $defaultAliasesLowerKeys = array_combine(
            array_keys(array_change_key_case(self::$_defaultAliases, CASE_LOWER)),
            array_keys(self::$_defaultAliases)
        );

        if (isset($defaultAliasesLowerKeys[$classLower])) {
            $classKey = $defaultAliasesLowerKeys[$classLower];
            if (isset(self::$_defaultAliases[$classKey])) {
                classAlias(self::$_defaultAliases[$classKey], $class);
                return class_exists($class, false);
            }
        }

        return false;
    }

    /**
     * Get default aliases list
     * 
     * @static
     * @return array
     */
    public static function getDefaultAliases()
    {
        if (self::$_defaultAliases === null) {
            self::fillDefaultAliasesWithMap();
        }
        return self::$_defaultAliases;
    }

    /**
     * Set default aliases list
     *
     * @static
     * @throws Akt_Exception
     * @param  array|null $aliases
     * @return void
     */
    public static function setDefaultAliases($aliases)
    {
        if (is_array($aliases) || $aliases === null) {
            self::$_defaultAliases = $aliases;
        }
        else {
            throw new Akt_Exception("Default aliases must be an array of aliases or null");
        }
    }

    /**
     * Load default aliases from map
     * 
     * @static
     * @return void
     */
    public static function fillDefaultAliasesWithMap()
    {
        self::$_defaultAliases = self::$_defaultAliasesMap;
    }

    /**
     * Remove default alias(es)
     *
     * @param string|array $alias
     * @return void
     */
    public static function removeDefaultAlias($alias = null)
    {
        if ($alias === null) {
            self::$_defaultAliases = array();
            return;
        }

        // Populate default aliases list if not initialized yet
        self::getDefaultAliases();

        if (!is_array(self::$_defaultAliases) || !self::$_defaultAliases) {
            self::$_defaultAliases = array();
            return;
        }

        if (is_string($alias)) {
            $alias = array($alias);
        }
        elseif (!is_array($alias)) {
            throw new Akt_Exception('Alias name must be a string or array of strings');
        }

        self::_removeAliasesFromArray(self::$_defaultAliases, $alias);
    }

    /**
     * Get default aliases map
     *
     * @static
     * @return array
     */
    public static function getDefaultAliasesMap()
    {
        return self::$_defaultAliasesMap;
    }

    /**
     * Set default aliases prefix
     *
     * @param string $prefix
     * @return void
     */
    public static function setDefaultAliasesPrefix($prefix)
    {
        self::$_defaultAliasesPrefix = (string) $prefix;
    }

    /**
     * Get current default aliases prefix
     *
     * @return string
     */
    public static function getDefaultAliasesPrefix()
    {
        return self::$_defaultAliasesPrefix;
    }

    /**
     * Try to load namespaced alias class
     *
     * @static
     * @param  string $class
     * @return bool
     */
    public static function loadNamespaceAliasClass($class)
    {
        if (!self::$_namespaces) {
            return false;
        }

        $classLower = strtolower($class);

        foreach (self::$_namespaces as $namespace => $paths)
        {
            $namespaceLower = strtolower($namespace);
            if (strpos($classLower, $namespaceLower . '_') !== 0) {
                continue;
            }

            $subpath = trim(substr($class, strlen($namespaceLower)), '_');

            foreach ($paths as $path) 
            {
                $fullpath = trim(str_replace('_', DIRECTORY_SEPARATOR, $path), '/\\')
                          . DIRECTORY_SEPARATOR
                          . trim(str_replace('_', DIRECTORY_SEPARATOR, $subpath), '/\\');
                
                if (Akt_Filesystem_File::exists($fullpath . '.php', true)) {
                    $fullClass = str_replace(DIRECTORY_SEPARATOR, '_', $fullpath);
                    classAlias($fullClass, $class);
                    if (class_exists($class, false)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Set path for namespace
     *
     * @param  string $namespace
     * @param  string|array $path
     * @return void
     */
    public static function setNamespace($namespace, $path)
    {
        $namespace = self::_formatNamespace($namespace);
        self::$_namespaces[$namespace] = array();
        self::addNamespacePath($namespace, $path);
    }

    /**
     * Add path for namespace
     *
     * @param  string $namespace
     * @param  string|array $path
     * @return void
     */
    public static function addNamespacePath($namespace, $path)
    {
        $namespace = self::_formatNamespace($namespace);

        if (!is_array(self::$_namespaces[$namespace])) {
            self::$_namespaces[$namespace] = array();
        }

        if (is_string($path)) {
            $path = array($path);
        }
        elseif (!is_array($path)) {
            throw new Akt_Exception("Path for namespace must be a string or an array of strings");
        }

        foreach ($path as $nspath)
        {
            $nspath = self::_formatNamespacePath($nspath);
            
            if (!in_array($nspath, self::$_namespaces[$namespace])) {
                self::$_namespaces[$namespace][] = $nspath;
            }
        }
    }

    /**
     * Remove namespace
     *
     * @param  string $namespace
     * @param  string|array|null $path
     * @return void
     */
    public static function removeNamespace($namespace, $path = null)
    {
        $namespace = self::_formatNamespace($namespace);

        if (!isset(self::$_namespaces[$namespace])) {
            return;
        }

        if ($path === null) {
            unset(self::$_namespaces[$namespace]);
            return;
        }

        if (is_string($path)) {
            $path = array($path);
        }
        elseif (!is_array($path)) {
            throw new Akt_Exception("Path for namespace must be a string or an array of strings");
        }

        foreach ($path as $nspath)
        {
            $nspath = self::_formatNamespacePath($nspath);

            if (($key = array_search($nspath, self::$_namespaces[$namespace])) !== false) {
                unset(self::$_namespaces[$namespace][$key]);
                if (!count(self::$_namespaces[$namespace])) {
                    unset(self::$_namespaces[$namespace]);
                    break;
                }
            }
        }
    }

    /**
     * Format namespace name
     * 
     * @static
     * @param  string $namespace
     * @return string
     */
    protected static function _formatNamespace($namespace)
    {
        return trim($namespace, '_');
    }

    /**
     * Format namespace path
     * 
     * @static
     * @param  string $path
     * @return string
     */
    protected static function _formatNamespacePath($path)
    {
        $path = str_replace('_', '/', $path);
        $path = trim(Akt_Filesystem_Path::clean($path), '/\\');

        if (Akt_Filesystem_Path::isAbsolute($path)) {
            throw new Akt_Exception("Namespace path must be local, relative and inside include_path");
        }
        
        return $path;
    }

    /**
     * Remove alises from specified array
     * 
     * Note: array is passing by reference
     *
     * @static
     * @throws Akt_Exception
     * @param  array $array
     * @param  array $aliases
     * @return void
     */
    protected static function _removeAliasesFromArray(&$array, $aliases)
    {
        if (!is_array($array) || !is_array($aliases)) {
            throw new Akt_Exception("Modified array and aliases must be array");
        }

        $arrayLowerKeys = array_combine(
            array_keys(array_change_key_case($array, CASE_LOWER)),
            array_keys($array)
        );

        foreach ($aliases as $classAlias)
        {
            if (!is_string($classAlias)) {
                throw new Akt_Exception('Class alias must be a string');
            }

            $classAlias = strtolower($classAlias);
            if (isset($arrayLowerKeys[$classAlias])) {
                $classAlias = $arrayLowerKeys[$classAlias];
                if (isset($array[$classAlias])) {
                    unset($array[$classAlias]);
                }
            }
        }
    }

    /**
     * Enable autoloader
     *
     * @return void
     */
    public static function enable()
    {
        self::$_disabled = false;
    }

    /**
     * Disable autoloader
     *
     * @return void
     */
    public static function disable()
    {
        self::$_disabled = true;
    }

    /**
     * Check if autoloader is disabled
     * 
     * @return boolean
     */
    public static function disabled()
    {
        return self::$_disabled;
    }
}

/**
 * Set class alias
 * 
 * Global function because class extending by eval() doesn't work inside class body
 *
 * @param string|array $class
 * @param string $alias 
 * @return void
 */
function classAlias($class, $alias = null)
{
    if (is_string($class) && is_string($alias)) {
        $class = array($class => $alias);
    }
    elseif (!is_array($class)) {
        throw new Akt_Exception("Class and alias must be strings or an array of aliases");
    }
    
    foreach ($class as $className => $aliasName) 
    {
        if (class_exists($aliasName, false)) {
            throw new Akt_Exception("Class {$aliasName} already exists");
        }
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            eval("class $aliasName extends $className {}");
        }
        else {
            class_alias($className, $aliasName);
        }
    }
}
