<?php

/**
 * 
 */
class Akt_Filesystem_Iterator_Append_AppendIterator extends AppendIterator
{
    /**
     * Rewind workaround object
     * @var Akt_Filesystem_Iterator_Append_RewindWorkaroundIterator
     */
    protected $_rewindWorkaround;
    
    
    /**
     * Appends an iterator
     * 
     * @param  Iterator $iterator
     * @return void
     */
    public function append(Iterator $iterator)
    {
        if (!$this->valid()
            && (!$this->_rewindWorkaround instanceof Akt_Filesystem_Iterator_Append_RewindWorkaroundIterator)
        ) {
            // workaround for AppendIterator's rewind calling for the first appended iterator
            $this->_rewindWorkaround = new Akt_Filesystem_Iterator_Append_RewindWorkaroundIterator();
            parent::append($this->_rewindWorkaround);
        }
        parent::append($iterator);
    }
    
    /**
     * Wrapper for parent's append() that doesn't support IteratorAggregate
     * 
     * @throws Akt_Exception
     * @param  Iterator|IteratorAggregate $iterator
     * @return void
     */
    public function appendTraversable($iterator)
    {
        if ($iterator instanceof IteratorAggregate) {
            $iterator = $iterator->getIterator();
        }
        elseif (!$iterator instanceof Iterator) {
            throw new Akt_Exception("Only Iterator and IteratorAggregate objects supported");
        }
        $this->append($iterator);
    }
}
