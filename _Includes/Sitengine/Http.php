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


abstract class Sitengine_Http
{

    
    public static function checkReferer()
    {
        $host = $_SERVER['HTTP_HOST'];
        $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
        return preg_match("/^https?:\/\/$host/", $referer);
    }
    
    
    
    public static function isPost()
    {
    	return preg_match('/^post$/i', $_SERVER["REQUEST_METHOD"]);
    }
    
    
    
    public static function isGet()
    {
    	return preg_match('/^get$/i', $_SERVER["REQUEST_METHOD"]);
    }

}

?>