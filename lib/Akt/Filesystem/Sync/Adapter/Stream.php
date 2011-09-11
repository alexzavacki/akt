<?php

/**
 * 
 */
class Akt_Filesystem_Sync_Adapter_Stream extends Akt_Filesystem_Sync_Adapter_AbstractAdapter
{
    /**
     * Source stream
     * @var Akt_Filesystem_Stream_FileStream
     */
    protected $_sourceStream;

    /**
     * Source files
     * @var array
     */
    protected $_source = array();

    /**
     * Target stream
     * @var Akt_Filesystem_Stream_FileStream
     */
    protected $_targetStream;
    
    /**
     * Target dir
     * @var string
     */
    protected $_target;
    
    
    /**
     * Constructor.
     * 
     * @param Akt_Filesystem_Stream_FileStream $targetStream
     * @param Akt_Filesystem_Stream_FileStream $sourceStream
     */
    public function __construct($targetStream = null, $sourceStream = null)
    {
        if ($sourceStream instanceof Akt_Filesystem_Stream_FileStream) {
            $this->setSourceStream($sourceStream);
        }
        if ($targetStream instanceof Akt_Filesystem_Stream_FileStream) {
            $this->setTargetStream($targetStream);
        }
    }
    
    /**
     * Do sync
     * 
     * @param mixed $target
     * @return void
     */
    public function sync($target = null)
    {
        $sourceStream = $this->getSourceStream();
        $targetStream = $this->getTargetStream();
        
        $source = $this->_source;
        $target = is_string($target) ? $target : $this->_target;
        
        if (!$source) {
            throw new Akt_Exception('Source files not set');
        }
        if (!is_string($target)) {
            throw new Akt_Exception('Target dir not set');
        }
        if (!$targetStream->isDir($target) || !$targetStream->isWritable($target)) {
            throw new Akt_Exception('Target dir does not exist or is not writable');
        }
        
        $lists = array();
        foreach ($source as $entry) 
        {
            if (is_string($entry)) 
            {
                if ($sourceStream->isDir($entry)) {
                    $lists[] = new Akt_Filesystem_List_FileList($entry, null, null, array(
                        'relative' => $entry
                    ));
                }
                elseif ($sourceStream->isFile($entry)) {
                    $dirname = dirname($entry);
                    $basename = basename($entry);
                    $lists[] = new Akt_Filesystem_List_FileList($dirname, array(
                        $basename
                    ), null, array(
                        'relative' => $dirname
                    ));
                }
                else {
                    continue;
                }
            }
            elseif (!$entry instanceof Akt_Filesystem_List_FileList) {
                continue;
            }
            $lists[] = $entry;
        }
        
        if ($lists) {
            foreach ($lists as $list) {
                $targetStream->upload($list, $target, true);
            }
        }
    }

    /**
     * Set source file stream
     * 
     * @param Akt_Filesystem_Stream_FileStream $sourceStream
     * @return Akt_Filesystem_Sync_Adapter_Stream
     */
    public function setSourceStream($sourceStream)
    {
        $this->_sourceStream = $sourceStream;
        return $this;
    }

    /**
     * Get source file stream
     * 
     * @return Akt_Filesystem_Stream_FileStream
     */
    public function getSourceStream()
    {
        if (!$this->_sourceStream instanceof Akt_Filesystem_Stream_FileStream) {
            $this->_sourceStream = new Akt_Filesystem_Stream_FileStream();
        }
        return $this->_sourceStream;
    }
    
    /**
     * Add source files
     * 
     * @param  mixed $source
     * @return Akt_Filesystem_Sync_Adapter_Stream
     */
    public function addSource($source)
    {
        if ($this->_source === null) {
            $this->_source = array();
        }
        elseif (!is_array($this->_source)) {
            $this->_source = array($this->_source);
        }
        
        if (is_array($source)) {
            $source = Akt_Helper_Array::flatten($source, true);
        }
        else {
            $source = array($source);
        }

        foreach ($source as $entry) {
            if (!in_array($entry, $this->_source, true)) {
                $this->_source[] = $entry;
            }
        }
        
        return $this;
    }

    /**
     * Set source files
     * 
     * @param array $source
     * @return Akt_Filesystem_Sync_Adapter_Stream
     */
    public function setSource($source)
    {
        $this->_source = array();
        $this->addSource($source);
        return $this;
    }
    
    /**
     * Clear all source files
     * 
     * @return Akt_Filesystem_Sync_Adapter_Stream
     */
    public function clearSource()
    {
        $this->_source = array();
        return $this;
    }

    /**
     * Get source files
     * 
     * @return array
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Set target file stream
     * 
     * @param Akt_Filesystem_Stream_FileStream $targetStream
     * @return Akt_Filesystem_Sync_Adapter_Stream
     */
    public function setTargetStream($targetStream)
    {
        $this->_targetStream = $targetStream;
        return $this;
    }

    /**
     * Get target file stream
     * 
     * @return Akt_Filesystem_Stream_FileStream
     */
    public function getTargetStream()
    {
        if (!$this->_targetStream instanceof Akt_Filesystem_Stream_FileStream) {
            $this->_targetStream = new Akt_Filesystem_Stream_FileStream();
        }        
        return $this->_targetStream;
    }

    /**
     * Set target dir
     * 
     * @param string $target
     * @return Akt_Filesystem_Sync_Adapter_Stream
     */
    public function setTarget($target)
    {
        $this->_target = $target;
        return $this;
    }

    /**
     * Get target dir
     * 
     * @return string
     */
    public function getTarget()
    {
        return $this->_target;
    }
}