<?php

/**
 * 
 */
class Akt_Helper_Class
{
    /**
     * Get parents of object or class
     * 
     * @static
     * @param  object|string $objectOrClass
     * @param  bool $includeSelf
     * @param  int $maxLevel
     * @return array
     */
    public static function getParents($objectOrClass, $includeSelf = false, $maxLevel = 0)
    {
        $parent = is_object($objectOrClass) ? get_class($objectOrClass) : (string) $objectOrClass;
        
        $stack = array($parent);
        while ($parent = get_parent_class($parent)) {
            $stack[] = $parent;
            if (--$maxLevel == 0) {
                break;
            }
        }
        
        if (!$includeSelf) {
            array_shift($stack);
        }

        return $stack;
    }
    
    /**
     * Check if string argument is abstract class
     * 
     * @static
     * @param  string $className
     * @return bool
     */
    public static function isAbstract($className)
    {
        $reflect = new ReflectionClass($className);
        return $reflect->isAbstract();
    }
    
    /**
     * Check if string argument is interface
     * 
     * @static
     * @param  string $interfaceName
     * @return bool
     */
    public static function isInterface($interfaceName)
    {
        $reflect = new ReflectionClass($interfaceName);
        return $reflect->isInterface();
    }
    
    /**
     * Check if string argument may be instantiated
     * 
     * @static
     * @param  string $class
     * @return bool
     */
    public static function isInstantiatable($class)
    {
        return !self::isAbstract($class) && !self::isInterface($class);
    }
}
