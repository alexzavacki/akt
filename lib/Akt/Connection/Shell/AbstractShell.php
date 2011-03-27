<?php

abstract class Akt_Connection_Shell_AbstractShell extends Akt_Connection_AbstractAdapter
{
    /**
     * Get adapter name
     *
     * @return string
     */
    public function getName()
    {
        return str_replace('akt_connection_shell_', '', strtolower(get_class($this)));
    }

    /**
     * ...
     */
    //abstract public function exec($command);
}