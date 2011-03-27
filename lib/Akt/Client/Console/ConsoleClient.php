<?php

/**
 * 
 */
class Akt_Client_Console_ConsoleClient extends Akt_Client_AbstractClient
{
    /**
     * @var Akt_Client_Console_Request
     */
    protected $_request;

    /**
     * @var string
     */
    protected $_defaultTask = 'default';


    /**
     * @param array $options
     */
    public function  __construct($options = array())
    {
        if ($options) {
            $this->setRequest(new Akt_Client_Console_Request($options));
        }
    }

    /**
     *
     */
    public function dispatch()
    {
        if (file_exists('aktfile.php')) {
            include_once 'aktfile.php';
        }
        $taskName = ($taskName = $this->getRequest()->getTask()) ? $taskName : $this->_defaultTask;
        Akt::loadTask($taskName);
        $arguments = $this->getRequest()->getTaskArguments();
        task($taskName, $arguments);
    }

    /**
     * @return object
     */
    public function getRequest()
    {
        if ($this->_request === null) {
            $this->_request = new Akt_Client_Console_Request();
        }
        return $this->_request;
    }

    /**
     * @param object $request
     * @return Akt_Client_Console_ConsoleClient
     */
    public function setRequest($request)
    {
        $this->_request = $request;
        return $this;
    }
}