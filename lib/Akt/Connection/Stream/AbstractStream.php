<?php

class Akt_Connection_Stream_AbstractStream extends Akt_Connection_AbstractAdapter
{
    /**
     * Get adapter name
     *
     * @return string
     */
    public function getName()
    {
        return str_replace('akt_connection_stream_', '', strtolower(get_class($this)));
    }
}