<?php

/**
 * ExcludeDirectoryFilterIterator filters out directories.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Akt_Filesystem_Filter_Iterator_ExcludeDirectoryFilterIterator extends FilterIterator
{
    /**
     * @var array
     */
    protected $_patterns = array();


    /**
     * Constructor.
     *
     * @param Iterator $iterator    The Iterator to filter
     * @param array    $directories An array of directories to exclude
     */
    public function __construct(Iterator $iterator, array $directories)
    {
        foreach ($directories as $directory) {
            $this->_patterns[] = '#(^|/)' . preg_quote($directory, '#') . '(/|$)#';
        }
        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return Boolean true if the value should be kept, false otherwise
     */
    public function accept()
    {
        $path = $this->isDir() ? $this->getSubPathname() : $this->getSubPath();
        $path = strtr($path, '\\', '/');
        
        foreach ($this->_patterns as $pattern) {
            if (preg_match($pattern, $path)) {
                return false;
            }
        }

        return true;
    }
}