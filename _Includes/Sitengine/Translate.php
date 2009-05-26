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


require_once 'Zend/Translate.php';


class Sitengine_Translate extends Zend_Translate
{
    
    const AN_XML = 'xml';
    
    private $_adapter;
    private static $_cache = null;
    
    
	public function setAdapter($adapter, $data, $locale = null, array $options = array())
    {
    	if($adapter == self::AN_XML)
    	{
    		require_once 'Sitengine/Translate/Adapter/Xml.php';
			$adapter = 'Sitengine_Translate_Adapter_Xml';
			
			if(self::$_cache !== null)
			{
				call_user_func(array($adapter, 'setCache'), self::$_cache);
			}
			$this->_adapter = new $adapter($data, $locale, $options);
		}
		/*
		else if($adapter == self::AN_XLIFF)
    	{
    		require_once 'Sitengine/Translate/Adapter/Xliff.php';
			$adapter = 'Sitengine_Translate_Adapter_Xliff';
			
			if(self::$_cache !== null)
			{
				call_user_func(array($adapter, 'setCache'), self::$_cache);
			}
			$this->_adapter = new $adapter($data, $locale, $options);
		}
		*/
		else {
    		parent::setAdapter($adapter, $data, $locale, $options);
    	}
    }
    
    
    public function getAdapter()
    {
        return $this->_adapter;
    }
    
    
    public static function getCache()
    {
        return self::$_cache;
    }
    
    
    public static function setCache(Zend_Cache_Core $cache)
    {
        self::$_cache = $cache;
    }
    
    
    public static function hasCache()
    {
        if (self::$_cache !== null) {
            return true;
        }

        return false;
    }
    
    
    public static function removeCache()
    {
        self::$_cache = null;
    }
    
    
    public static function clearCache()
    {
        self::$_cache->clean();
    }
    
    
    public function __call($method, array $options)
    {
        if (method_exists($this->_adapter, $method)) {
            return call_user_func_array(array($this->_adapter, $method), $options);
        }
        require_once 'Zend/Translate/Exception.php';
        throw new Zend_Translate_Exception("Unknown method '" . $method . "' called!");
    }
}
?>