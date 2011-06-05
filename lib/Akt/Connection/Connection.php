<?php

/**
 * @todo: Replace ConnectionHandle with Session
 */

class Akt_Connection_Connection
{
    /**
     * @var Akt_Connection_Shell_AbstractShell
     */
    protected $_shell;

    /**
     * @var Akt_Connection_Stream_AbstractStream
     */
    protected $_stream;

    /**
     * Shell and stream have to use one shared connection if this is possible
     * @var bool
     */
    protected $_useSharedConnection = true;

    /**
     * Adapted-specific shared connection handle
     * @var mixed
     */
    protected $_sharedConnectionHandle;


    /**
     * Constructor
     *
     * @param string $shell
     * @param string|array $streamOrParams
     * @param array $params
     */
    public function  __construct($shell, $streamOrParams = null, $params = array())
    {
        $stream = null;
        if (is_string($streamOrParams)) {
            $stream = $streamOrParams;
        }
        elseif (is_array($streamOrParams)) {
            $stream = $shell;
            $params = $streamOrParams;
        }
        elseif ($streamOrParams === true) {
            $stream = $shell;
        }

        if ($shell) {
            $this->setShell($shell);
            $shellParams = $params;
            if (isset($params['shell'])) {
                $shellParams = array_merge($shellParams, $params['shell']);
            }
            $this->getShell()->setOptions($shellParams);
        }

        if ($stream) {
            $this->setStream($stream);
            $streamParams = $params;
            if (isset($params['stream'])) {
                $streamParams = array_merge($streamParams, $params['stream']);
            }
            $this->getStream()->setOptions($streamParams);
        }
    }

    /**
     * Calling of nonexistent method
     *
     * @param  string $method
     * @param  array  $args
     * @return void
     */
    public function __call($method, $args)
    {
        $adapters = array($this->_shell, $this->_stream);

        foreach ($adapters as $adapter) {
            $methods = $this->getAdapterMethods($adapter);
            if (in_array($method, $methods)) {
                return call_user_func_array(array($adapter, $method), $args);
            }
        }

        throw new Akt_Exception("Method " . __CLASS__ . "::{$method}() not found");
    }

    /**
     * Get available and callable adapter methods
     *
     * @param Akt_Connection_AbstractAdapter $adapter
     * @return array
     */
    public function getAdapterMethods($adapter)
    {
        $result = array();

        if ($adapter instanceof Akt_Connection_AbstractAdapter) {
            //$methods = $adapter->getAdapterMethods();
            $methods = get_class_methods($adapter);
            $result = array_merge($result, $methods);
        }

        return $result;
    }

    /**
     * Connect
     *
     * @return void
     */
    public function connect()
    {
        if ($this->_shell) {
            $this->_shell->connect();
        }
        if ($this->_stream) {
            $this->_stream->connect();
        }
    }

    /**
     * Set or get (if null passed) using shared connection
     *
     * @param bool|null $value
     * @return Akt_Connection_Connection|bool
     */
    public function useSharedConnection($value = null)
    {
        if ($value === null) {
            return $this->_useSharedConnection;
        }
        $this->_useSharedConnection = (bool) $value;
        return $this;
    }

    /**
     * Set shell adapter
     *
     * @param string|Akt_Connection_Shell_AbstractShell $shell
     * @return Akt_Connection_Connection
     */
    public function setShell($shell)
    {
        if ($shell === null) {
            if (!$this->_shell instanceof Akt_Connection_Shell_AbstractShell) {
                $this->_shell = null;
                return $this;
            }
        }
        elseif (is_string($shell)) {
            $shellClassName = $this->formatShellClassName($shell);
            $shell = new $shellClassName();
        }
        elseif (!$shell instanceof Akt_Connection_Shell_AbstractShell) {
            throw new Akt_Exception("Shell parameter must be a string or an Akt_Connection_Shell_AbstractShell");
        }

        $oldShell = $this->_shell;
        
        $this->_shell = $shell;
        if ($this->_shell instanceof Akt_Connection_Shell_AbstractShell) {
            $this->_shell->setConnection($this);
        }

        if (($oldShell instanceof Akt_Connection_Shell_AbstractShell)
            && $oldShell !== $shell
            && $oldShell->getConnection() === $this)
        {
            $oldShell->setConnection(null);
        }

        return $this;
    }

    /**
     * Get shell adapter
     *
     * @return Akt_Connection_Shell_AbstractShell
     */
    public function getShell()
    {
        return $this->_shell;
    }

    /**
     * Get full class name for provided shell
     *
     * @param string $shell
     * @return string
     */
    public function formatShellClassName($shell)
    {
        return 'Akt_Connection_Shell_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', $shell)));
    }

    /**
     * Set stream adapter
     *
     * @param string|Akt_Connection_Stream_AbstractStream $stream
     * @return Akt_Connection_Connection
     */
    public function setStream($stream)
    {
        if (is_string($stream)) {
            $streamClassName = $this->formatStreamClassName($stream);
            $stream = new $streamClassName();
        }
        elseif (!$stream instanceof Akt_Connection_Stream_AbstractStream) {
            throw new Akt_Exception("Stream parameter must be a string or an Akt_Connection_Stream_AbstractStream");
        }

        if (($this->_stream instanceof Akt_Connection_Stream_AbstractStream)
            && $this->_stream->getConnection() === $this)
        {
            $this->_stream->setConnection(null);
        }

        $this->_stream = $stream;
        return $this;
    }

    /**
     * Get stream adapter
     *
     * @return Akt_Connection_Stream_AbstractStream
     */
    public function getStream()
    {
        return $this->_stream;
    }

    /**
     * Get full class name for provided stream wrapper
     *
     * @param string $stream
     * @return string
     */
    public function formatStreamClassName($stream)
    {
        return 'Akt_Connection_Stream_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', $stream)));
    }
}