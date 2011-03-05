<?php

if (php_sapi_name() == 'cli')
{
    try {
        $args = isset($_SERVER['argv'])
            ? $_SERVER['argv']
            : (isset($argv) ? $argv : array());
        akt_main($args);
    }
    catch (Exception $e) {
        echo "\nAkt execution terminated: " . $e->getMessage() . "\n";
        exit(($code = $e->getCode()) > 1 ? $code : 1);
    }
    exit(0);
}

/**
 * Akt php script
 *
 * @param array $args
 * @return void
 */
function akt_main($args)
{
    if (!akt_load_bootstrap()) {
        akt_display_error();
        throw new Exception('Bootstrap loading failed');
    }
    
    if (isset($args[0]) && realpath($args[0]) == __FILE__) {
        array_shift($args);
    }

    Akt::run('console', $args);
}

/**
 * Try to load lib bootstrap
 * @return 1|false
 */
function akt_load_bootstrap()
{
    return @include_once dirname(__FILE__) . '/../lib/Akt.php';
}

/**
 * Show loading error message
 * @return void
 */
function akt_display_error()
{
    echo <<<ERRORMSG

  *** Akt deployment tool ***
        
Unable to load Akt bootstrap file lib/Akt.php
Ensure that this file exists and is readable

ERRORMSG;
}
