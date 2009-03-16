<?php

/**
 * Sitengine - An example serving as a pattern
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Sitengine
 * @package    Sitengine
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */

    

 
abstract class Sitengine_Regex
{

    
    public static function getServerName()
    {
        return '([a-zA-Z0-9][\w\d-]*[a-zA-Z0-9]\.)*[a-zA-Z0-9][\w\d-]*[a-zA-Z0-9]';
    }
    
    
    public static function getFileName()
    {
        return '[^\r\n\t\/\:\*\?"<>\|\\\]+';
        #return '[^\/\:\*\?"<>\|\\\]+';
    }
    
    
    public static function getMailbox()
    {
        return '[a-zA-Z][\w\d\.-]*(?<![\._-])@';
    }
    
    
    public static function getEmail()
    {
        return '[a-zA-Z][\w\d\.-]*(?<![\._-])@'.self::getServerName();
    }
    
}
?>