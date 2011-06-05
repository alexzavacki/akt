<?php

class Akt_Log
{
    /**
     * Singleton instance
     * @var Akt_Log 
     */
    protected static $_instance;
    
    /**
     * Is debug mode enabled
     * @var bool 
     */
    protected static $_isDebug = false;

    
    /**
     * Singleton
     */
    protected function __construct() {}
    protected function __clone() {}

    /**
     * Get log instance
     *
     * @return Akt_Log 
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Write text to log
     *
     * @param string $text
     * @param bool $lf 
     * @return void
     */
    public static function write($text = '', $lf = true)
    {
        if ($lf) {
            $text .= "\n";
        }
        fwrite(STDOUT, $text);
    }
    
    /**
     * Write text to log if debug mode is enabled
     *
     * @param string $text
     * @param bool $lf 
     * @return void
     */
    public static function debug($text, $lf = true)
    {
        if (self::$_isDebug) {
            self::write($text, $lf);
        }
    }
    
    /**
     * Write $count empty lines to log
     *
     * @param int $count 
     * @return void
     */
    public static function line($count = 1)
    {
        self::write(str_repeat("\n", (int) $count), false);
    }
    
    /**
     * Set debug mode
     *
     * @param bool $value 
     * @return void
     */
    public static function setDebug($value = true)
    {
        self::$_isDebug = (bool) $value;
    }
}
