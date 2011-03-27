<?php

/**
 *
 */
class Akt_Client_Console_Request
{
    /**
     * @var array
     */
    protected $_args = array();

    /**
     * @var Akt_Client_Console_Getopt
     */
    protected $_getopt;


    /**
     * Constructor
     * @param array $args
     */
    public function  __construct($args = array())
    {
        $this->getopt()->addRules(array(
            'interactive|i' => 'Interactive mode'
        ));

        if ($args) {
            $this->_args = $args;
            $this->getopt()->addArguments($args);
        }
    }

    /**
     * @return string
     */
    public function getTask()
    {
        $arguments = $this->getopt()->getRemainingArgs();
        return count($arguments) ? array_shift($arguments) : null;
    }

    /**
     * @return string
     */
    public function getTaskArguments()
    {
        $arguments = $this->getopt()->getRemainingArgs();
        return count($arguments) > 1 ? array_slice($arguments, 1) : array();
    }

    /**
     * @return bool
     */
    public function isInteractive()
    {
        return isset($this->getopt()->interactive);
    }

    /**
     * @return Akt_Client_Console_Getopt
     */
    public function getopt()
    {
        if ($this->_getopt === null) {
            $this->_getopt = new Akt_Client_Console_Getopt(array(), array());
        }
        return $this->_getopt;
    }
}