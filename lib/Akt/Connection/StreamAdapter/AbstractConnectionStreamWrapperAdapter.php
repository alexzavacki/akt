<?php

abstract class Akt_Connection_StreamAdapter_AbstractConnectionStreamWrapperAdapter
    extends Akt_Filesystem_Stream_Adapter_AbstractStreamWrapperAdapter
    implements Akt_Connection_StreamAdapter_ConnectionStreamAdapter
{
    /**
     * Connection session
     * @var Akt_Connection_Connection
     */
    protected $_connection;
    
    
    /**
     * Constructor.
     * 
     * @param Akt_Connection_Connection $connection
     */
    public function __construct($connection = null)
    {
        if ($connection !== null) {
            $this->setConnection($connection);
        }
    }

    /**
     * Set connection session
     * 
     * @param  Akt_Connection_Connection $connection
     * @return Akt_Connection_StreamAdapter_AbstractConnectionStreamWrapperAdapter
     */
    public function setConnection($connection)
    {
        $this->_connection = $connection;
        return $this;
    }
    
    /**
     * Get connection session
     * 
     * @return Akt_Connection_Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }
}
