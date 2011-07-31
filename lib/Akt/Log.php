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
    protected $_isDebug = false;
    
    /**
     * Indent string
     * @var string
     */
    protected $_indentString;
    
    /**
     * Current indent level
     * @var int
     */
    protected $_indentLevel = 0;
    
    /**
     * Is new line?
     * @var bool
     */
    protected $_newLine = true;

    
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
     * @return Akt_Log
     */
    public function write($text = '', $lf = true)
    {
        if ($lf) {
            $text .= "\n";
        }
        if ($this->_newLine && $this->_indentLevel) {
            $text = str_repeat($this->getIndentString(), (int) $this->_indentLevel) . $text;
        }
        $this->_newLine = substr($text, -1) == "\n";
        fwrite(STDOUT, $text);
        return $this;
    }
    
    /**
     * Write $count empty lines to log
     *
     * @param int $count 
     * @return Akt_Log
     */
    public function line($count = 1)
    {
        $this->write(str_repeat("\n", (int) $count), false);
        return $this;
    }
    
    /**
     * Write text to log if debug mode is enabled
     *
     * @param string $text
     * @param bool $lf 
     * @return Akt_Log
     */
    public function debug($text, $lf = true)
    {
        if ($this->_isDebug) {
            $this->write($text, $lf);
        }
        return $this;
    }

    /**
     * Set debug mode
     *
     * @param bool $value 
     * @return Akt_Log
     */
    public function setDebug($value = true)
    {
        $this->_isDebug = (bool) $value;
        return $this;
    }

    /**
     * Set indent string
     * 
     * @param  string $value
     * @return Akt_Log
     */
    public function setIndentString($value)
    {
        $this->_indentString = $value;
        return $this;
    }

    /**
     * Get indent string
     * 
     * @return string
     */
    public function getIndentString()
    {
        if ($this->_indentString === null) {
            $this->_indentString = str_repeat(' ', 4);
        }
        return $this->_indentString;
    }

    /**
     * Set current indent level
     * 
     * @param  int $indentLevel
     * @return Akt_Log
     */
    public function setIndentLevel($indentLevel)
    {
        $this->_indentLevel = (int) $indentLevel;
        return $this;
    }

    /**
     * Add one indent level
     * 
     * @return Akt_Log
     */
    public function addIndentLevel()
    {
        if (!$this->_indentLevel) {
            $this->_indentLevel = 0;
        }
        ++$this->_indentLevel;
        return $this;
    }

    /**
     * Sub one indent level
     * 
     * @return Akt_Log
     */
    public function subIndentLevel()
    {
        $this->_indentLevel = max($this->_indentLevel - 1, 0);
        return $this;
    }

    /**
     * Get current indent level
     * 
     * @return int
     */
    public function getIndentLevel()
    {
        return $this->_indentLevel;
    }
}
