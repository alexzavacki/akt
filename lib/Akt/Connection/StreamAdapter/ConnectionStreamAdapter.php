<?php

/**
 * 
 */
interface Akt_Connection_StreamAdapter_ConnectionStreamAdapter
{
    /**
     * Set connection session
     * @param  Akt_Connection_Connection $connection
     * @return Akt_Connection_StreamAdapter_ConnectionStreamAdapter
     */
    public function setConnection($connection);
    
    /**
     * Get connection session
     * @return Akt_Connection_Connection
     */
    public function getConnection();  
}
