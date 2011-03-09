<?php

/* ==============================
     Set errors output settings
// ============================== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ==============================
     Set required include paths
// ============================== */
$includePath = array();
foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
    if ($path != '.' && ($path = realpath($path))) {
        $includePath[] = $path;
    }
}
$includePath = array_unique(
    array_merge(
        array(
            getcwd(),
            dirname(__FILE__),
            realpath(dirname(__FILE__) . '/vendors'),
        ),
        $includePath
    )
);
set_include_path(implode(PATH_SEPARATOR, $includePath));

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
     * Check is task exists
     *
     * @param string $taskName
     * @return bool
     */
    public static function taskExists($taskName)
    {
        $functionName = 'task_' . $taskName;
        if (function_exists($functionName)) {
            return true;
        }

        $className = self::formatTaskClassName($taskName);
        if (class_exists($className, false)) {
            return true;
        }

        return false;
    }

    /**
     * Loads task by its name
     *
     * @param string $taskName
     * @return void
     */
    public static function loadTask($taskName)
    {
        if (self::taskExists($taskName)) {
            return;
        }

        $path = explode('_', $taskName);

        $taskPath = count($path) > 1 ? implode(DIRECTORY_SEPARATOR, array_slice($path, 0, -1)) : '';
        $taskFilename = end($path) . '.php';

        $functionName = 'task_' . $taskName;
        $className = self::formatTaskClassName($taskName);

        $filename = 'tasks' . DIRECTORY_SEPARATOR . $taskPath . DIRECTORY_SEPARATOR . $taskFilename;

        if (file_exists($filename)) {
            include_once($filename);
        }

        if (self::taskExists($taskName)) {
            return;
        }

        throw new Akt_Exception("Task '$taskName' not found");
    }

    /**
     * Get task's class name
     *
     * @param string $taskName
     * @return string
     */
    public static function formatTaskClassName($taskName)
    {
        return str_replace(' ', '_', ucwords(str_replace('_', ' ', $taskName))) . 'Task';
    }
}


/* =====================================================
     Global functions (mostly aliases for Akt methods)
// ===================================================== */

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
function module($name, $instance = 'default')
{
    //
}

/**
 * Execute task by its name
 *
 * @param string $taskName
 * @param array $params
 * @return mixed
 */
function task($taskName, $params = array())
{
    $taskFunction = 'task_' . $taskName;
    $taskClassName = Akt::formatTaskClassName($taskName);

    Akt::loadTask($taskName);
    
    if (function_exists($taskFunction))
    {
        if (Akt_Helper_Array::hasStringKey($params)) {
            $params = array($params);
        }
        return call_user_func_array($taskFunction, $params);
    }
    elseif (class_exists($taskClassName, false))
    {
        $class = new $taskClassName();
        foreach ($params as $key => $value) {
            if (is_string($key) && property_exists($class, $key)) {
                $class->$key = $value;
            }
        }
        return $class->execute();
    }

    return false;
}

/**
 * Execute task if it wasn't executed before
 *
 * If task needs parameters, this task must be provided by array:
 *   array('taskName', array(param1, param2, ...))
 *
 * You can also specify bool parameter 'force' in "task array",
 * which means that task must be run even if it has been already executed.
 * By default this parameter is false:
 *     array('taskName', array(param1, param2, ...), true)
 *     or array('taskName', true), if no parameters needed
 * In this case this is the same as task() function calling,
 * but may be useful on multiple task depending:
 *     depends('task1', array('task2', true), 'task3');
 *       instead of
 *     depends('task1');
 *     task('task2');
 *     depends('task3');
 *
 *
 * @staticvar array $executedTasks
 * @return void
 */
function depends()
{
    static $executedTasks = array();

    $tasks = array();
    $defaultParams = array();
    $defaultForce = false;

    $args = func_get_args();
    $argc = count($args);

    if (!$argc) {
        return;
    }

    foreach ($args as $arg)
    {
        if (is_string($arg)) {
            $arg = array($arg);
        }
        elseif (!is_array($arg)) {
            continue;
        }
        $taskName = array_shift($arg);
        $taskParams = $arg ? array_shift($arg) : $defaultParams;
        if (is_bool($taskParams)) {
            $taskForce = $taskParams;
            $taskParams = $defaultParams;
        }
        else {
            $taskForce = $arg ? array_shift($arg) : $defaultForce;
        }
        $tasks[] = array(
            'name' => $taskName,
            'params' => $taskParams,
            'force' => $taskForce,
        );
    }

    foreach ($tasks as $task)
    {
        $alreadyExecuted = in_array(strtolower($task['name']), $executedTasks);
        if (!$alreadyExecuted || $task['force'])
        {
            task($task['name'], $task['params']);
            if (!$alreadyExecuted) {
                $executedTasks[] = strtolower($task['name']);
            }
        }
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

        // @todo: class_alias() for php >= 5.3.0
        eval("class $classAlias extends $className {}");
    }
}
