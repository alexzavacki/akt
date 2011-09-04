<?php

/**
 * Workaround for standard AppendIterator class
 * For more details see: https://bugs.php.net/bug.php?id=49104
 * 
 * Iterator's valid() method return true only after first rewind
 * to help AppendIterator become valid, but then returns false while real iterating
 */
class Akt_Filesystem_Iterator_Append_RewindWorkaroundIterator implements Iterator
{
    /**
     * Rewind count
     * @var int
     */
    private $_rewinded = 0;
    
    public function rewind() { ++$this->_rewinded; }
    public function valid()  { return $this->_rewinded <= 1; }
    
    public function current() {return null;}
    public function key()     {return 0;}
    public function next()    {}
}
