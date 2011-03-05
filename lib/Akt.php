<?php

/* ==============================
     Set errors output settings
// ============================== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ==============================
     Set required include paths
// ============================== */
$aktIncludePath = array();
foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
    if ($path != '.' && ($path = realpath($path))) {
        $aktIncludePath[] = $path;
    }
}
$aktIncludePath = array_unique(
    array_merge(
        array(
            getcwd(),
            dirname(__FILE__),
            realpath(dirname(__FILE__) . '/vendors'),
        ),
        $aktIncludePath
    )
);
set_include_path(implode(PATH_SEPARATOR, $aktIncludePath));

/* ==========================================================
     Create Akt Autoloader and register it by instantiating
// ========================================================== */

require_once 'Akt/Loader/Autoloader.php';
Akt_Loader_Autoloader::getInstance();


/**
 *
 */
class Akt
{
    /**
     * Run Akt tool
     *
     * @param string $client
     * @param mixed $options
     * @return mixed
     */
    public static function run($client, $options = null)
    {
        $clientName = ucfirst($client);
        $className = 'Akt_Client_' . $clientName . '_' . $clientName . 'Client';

        if (!class_exists($className)) {
            throw new Akt_Exception("Akt client '$client' not found");
        }

        $client = new $className($options);
        return $client->dispatch();
    }

    /**
     * Loads module by its name and instantiate it
     *
     * By default instantiate each module once (Singleton)
     * If you need new instance of some module specify $instance parameter
     *
     * @param string $name
     * @param string $instance
     * @return object
     */
    public static function module($name, $instance = 'default')
    {
        return $name ? $name : $instance;
    }

    /**
     * Register an alias for long class name
     *
     * It is alias for registerClassAlias() function
     *
     * @param string|array $alias
     * @return void
     */
    public static function registerClassAlias($alias)
    {
        return registerClassAlias($alias);
    }
}


/* =====================================================
     Global functions (mostly aliases for Akt methods)
// ===================================================== */

/**
 * Alias for Akt::module()
 *
 * @param string $name
 * @param string $instance
 * @return object
 */
function module($name, $instance = 'default')
{
    return Akt::module($name, $instance);
}

/**
 *
 */
function depends()
{
    $args = func_get_args();

    $resultStack = array();

    foreach ($args as $task)
    {
        $taskfunc = 'task_' . $task;
        $taskClass = ucfirst($task) . 'Task';
        if (function_exists($taskfunc)) {
            $taskResult = $taskfunc($resultStack);
        }
        elseif (class_exists($taskClass, false)) {
            $class = new $taskClass();
            $taskResult = $class->execute($resultStack);
        }
        $resultStack[] = $taskResult;
    }
}

/**
 * Register an alias for long class name
 *
 * There are some default Akt aliases:
 * 
 *   Config -> Akt_Config
 *   path   -> Akt_Helper_Filesystem_Path
 *   dir    -> Akt_Helper_Filesystem_Dir
 *   file   -> Akt_Helper_Filesystem_File
 *
 * @param string|array $alias
 * @return void
 */
function registerClassAlias($alias)
{
    if (is_string($alias)) {
        $alias = array($alias);
    }
    elseif (!is_array($alias)) {
        throw new Akt_Exception("Alias must be a string or an array");
    }

    $aktClassesMap = array(
        'Config' => 'Akt_Config',
        'path' => 'Akt_Helper_Filesystem_Path',
        'dir'  => 'Akt_Helper_Filesystem_Dir',
        'file' => 'Akt_Helper_Filesystem_File',
    );

    // array_change_key_case($array, CASE_LOWER);
    $aktClassesMapLowerKeys = array_combine(
        array_map('strtolower', array_keys($aktClassesMap)),
        array_keys($aktClassesMap)
    );

    foreach ($alias as $classAlias => $className)
    {
        if (!is_string($className)) {
            throw new Akt_Exception('Class name must be a string');
        }

        if (is_numeric($classAlias)) 
        {
            $classAlias = $className;
            $className = strtolower($className);
            if (isset($aktClassesMap[$className])) {
                $className = $aktClassesMap[$className];
            }
            elseif (isset($aktClassesMapLowerKeys[$className])) {
                $className = $aktClassesMap[$aktClassesMapLowerKeys[$className]];
            }
            else {
                throw new Akt_Exception("Class for alias '$className' not found");
            }
        }

        if (class_exists($classAlias, false)) {
            throw new Akt_Exception("Class '$classAlias' already exists");
        }

        if (!class_exists($className)) {
            throw new Akt_Exception("Class '$className' not found");
        }

        eval("class $classAlias extends $className {}");
    }
}
