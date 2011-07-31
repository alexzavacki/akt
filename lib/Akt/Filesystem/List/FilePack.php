<?php

/**
 * 
 */
class Akt_Filesystem_List_FilePack
{
    /**
     * Pack's filename
     * @var string
     */
    protected $_filename;
    
    /**
     * Source files list
     * @var Akt_Filesystem_List_FileList|array
     */
    protected $_filelist;
    
    /**
     * Pack options
     * @var Akt_Options
     */
    protected $_options;
    
    
    /**
     * Constructor.
     *
     * @param string $filename
     * @param Akt_Filesystem_List_FileList|array $filelist
     * @param array|string|Akt_Options $options 
     */
    public function __construct($filename, $filelist, $options = array()) 
    {
        $this->_filename = $filename;
        $this->_filelist = $filelist;
        
        if (!$options instanceof Akt_Options) {
            $options = new Akt_Options($options, 'glue');
        }
        $this->_options = $options;
    }
    
    /**
     * Pack all source files
     * 
     * @return void
     */
    public function pack()
    {
        if (file_exists($this->_filename)) {
            Akt_Filesystem_File::remove($this->_filename);
        }
        
        Akt_Filesystem_File::create(
            $this->_filename, 
            $this->options()->get('chmod', 0777), 
            $this->options()->get('dirchmod')
        );
        
        $this->beforePack();
        
        if ($this->_filelist instanceof Akt_Filesystem_List_FileList) {
            $filelist = $this->_filelist->get();
        }
        elseif (is_array($this->_filelist)) 
        {
            $depth = 0;
            $subpaths = array();
            $filelist = array();

            $it = new RecursiveArrayIterator($this->_filelist);
            $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($it as $key => $entry) 
            {
                if (is_array($entry)) {
                    $subpaths[] = $key;
                    $depth++;
                    continue;
                }
                if ($depth > $it->getDepth() || count($subpaths) > $it->getDepth()) {
                    $depth = $it->getDepth();
                    $subpaths = array_slice($subpaths, 0, $depth);
                }
                $path = $entry;
                if ($subpaths) {
                    $path = implode('/', $subpaths) . '/' . $path;
                }
                $filelist[] = Akt_Filesystem_Path::clean($path);
            }
        }
        else {
            throw new Akt_Exception("Filelist must be an array or "
                . "instance of Akt_Filesystem_List_FileList");
        }
        
        $glue = $this->getGlue();

        foreach ($filelist as $key => $filename) {
            $content = $key > 0 ? $glue : '';
            $content .= $this->filter(Akt_Filesystem_File::read($filename));
            Akt_Filesystem_File::append($this->_filename, $content);
        }

        $this->afterPack();
    }
    
    /**
     * Hooks
     */
    public function beforePack() {}
    public function afterPack() {}
    
    /**
     * Filter content before appending
     * 
     * This method is calling for each list entry
     *
     * @param  string $content
     * @return string 
     */
    public function filter($content) { return $content; }
    
    /**
     * Get pack's filename
     *
     * @return string 
     */
    public function getFilename()     
    {
        return $this->_filename;
    }

    /**
     * Set pack's filename
     *
     * @param  string $filename
     * @return Akt_Filesystem_List_FilePack 
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;
        return $this;
    }

    /**
     * Get source files list
     *
     * @return Akt_Filesystem_List_FileList|array
     */
    public function getFilelist()
    {
        return $this->_filelist;
    }

    /**
     * Set source files list
     *
     * @param  Akt_Filesystem_List_FileList|array $filelist
     * @return Akt_Filesystem_List_FilePack 
     */
    public function setFilelist($filelist)
    {
        $this->_filelist = $filelist;
        return $this;
    }

    /**
     * Get options adapter
     *
     * @return Akt_Options
     */
    public function options()
    {
        if (!$this->_options instanceof Akt_Options) {
            $this->_options = new Akt_Options();
        }
        return $this->_options;
    }
    
    /**
     * Set options adapter
     *
     * @param  Akt_Options $options
     * @param  bool $clone
     * @return Akt_Filesystem_List_FilePack 
     */
    public function setOptions($options, $clone = false)     
    {
        if (!$options instanceof Akt_Options) {
            throw new Akt_Exception('Options must be an instance of Akt_Options');
        }
        $this->_options = $clone ? (clone $options) : $options;
        return $this;
    }

    /**
     * Get glue string
     *
     * @return string 
     */
    public function getGlue()     
    {
        return $this->options()->get('glue', "\n");
    }

    /**
     * Set glue string
     *
     * @param  string $glue
     * @return Akt_Filesystem_List_FilePack 
     */
    public function setGlue($glue)
    {
        $this->options()->set('glue', (string) $glue);
        return $this;
    }
}