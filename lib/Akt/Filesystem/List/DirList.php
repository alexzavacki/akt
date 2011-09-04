<?php

/**
 * 
 */
class Akt_Filesystem_List_DirList extends Akt_Filesystem_List_AbstractList
{
    /**
     * List's mode
     * @var int
     */
    protected $_mode = self::DIRS_ONLY;

    
    /**
     * Create and return new dirlist
     * 
     * @static
     * @param  int|string|array $basedirOrInclude
     * @param  array|null $includeOrExclude
     * @param  array|null $excludeOrOptions
     * @param  array|int $options
     * @return Akt_Filesystem_List_DirList
     */
    public static function create($basedirOrInclude = self::PARENT, 
        $includeOrExclude = null, $excludeOrOptions = null, $options = null
    ) {
        return new self($basedirOrInclude, $includeOrExclude, $excludeOrOptions, $options);
    }
}