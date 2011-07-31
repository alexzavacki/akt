<?php

interface Akt_Connection_Adapter_Ssh_Libssh2_Authentication_Authentication
{
    /**
     * Authenticate session
     *
     * @param  Akt_Connection_Adapter_Ssh_Libssh2_Session|resource $session
     * @return bool
     */
    function authenticate($session);
}
