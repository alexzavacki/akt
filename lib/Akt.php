<?php

/* ==============================
     Set errors output settings
// ============================== */
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

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
            realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendors'),
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
     * Current client instance
     * @var Akt_Client_AbstractClient 
     */
    protected static $_client;
    
    
    /**
     * Run Akt tool
     *
     * @param string $client
     * @param mixed $options
     * @return mixed
     */
    public static function run($client, $options = null)
    {
        $className = self::formatClientClassName($client);

        if (!class_exists($className)) {
            throw new Akt_Exception("Akt client '$client' not found");
        }
        
        self::$_client = new $className($options);
        return self::$_client->dispatch();
    }
    
    /**
     * Get current client instance
     *
     * @return Akt_Client_AbstractClient 
     */
    public static function getClient()
    {
        return self::$_client;
    }
    
    /**
     * Get client's class name
     *
     * @param string $clientName
     * @return string
     */
    public static function formatClientClassName($clientName)
    {
        $clientName = ucfirst($clientName);
        return 'Akt_Client_' . $clientName . '_' . $clientName . 'Client';
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

        $taskPath = count($path) > 1 
            ? implode(DIRECTORY_SEPARATOR, array_slice($path, 0, -1)) 
            : '';
        $taskFilename = end($path) . '.php';

        $functionName = 'task_' . $taskName;
        $className = self::formatTaskClassName($taskName);

        $filename = implode(DIRECTORY_SEPARATOR, array('tasks', $taskPath, $taskFilename));

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
        return $class->execute($params);
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
 * Get log instance
 *
 * @return Akt_Log 
 */
function getLog()
{
    return Akt_Log::getInstance();
}

/**
 * Add include path
 *
 * @param string $path 
 * @return void
 */
function addIncludePath($path)
{
    if (is_string($path)) {
        $path = array($path);
    }
    elseif (!is_array($path)) {
        throw new Akt_Exception("Include path must be a string or an array");
    }
    
    $currentIncludePath = explode(PATH_SEPARATOR, get_include_path());
    $path = array_diff(array_filter(array_map('realpath', $path)), $currentIncludePath);
    
    if ($path) {
        set_include_path(implode(PATH_SEPARATOR, array_merge($currentIncludePath, $path)));
    }
}

/**
 * Register alias

 * @param string|array $alias
 * @return void
 */
function registerClassAlias($alias)
{
    Akt_Loader_ClassAliasLoader::registerAlias($alias);
}