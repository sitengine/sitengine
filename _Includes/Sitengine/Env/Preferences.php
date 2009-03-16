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



class Sitengine_Env_Preferences
{
    
    protected static $_instance = null;
    protected $_namespace = null;
    
    
    
    

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    private function __construct()
    {
    	require_once 'Zend/Session/Namespace.php';
    	$this->_namespace = new Zend_Session_Namespace(get_class($this));
    }

    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    private function __clone()
    {}

    /**
     * Returns an instance of Sitengine_Env_Preferences
     *
     * Singleton pattern implementation
     *
     * @return Sitengine_Env_Preferences Provides a fluent interface
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    
    
    
    
    
    
    protected $_timezone = null;
    
    public function establishTimezone(Sitengine_Controller_Request_Http $request, $param)
    {
        $value = $this->_establishValue($request, $param);
        $this->_timezone = $value;
        return $value;
    }
    
    
    public function getTimezone()
    {
    	return (@timezone_open($this->_timezone)) ? $this->_timezone : 'UTC';
    }
    
    
    public function setTimezone($timezone)
    {
        $this->_timezone = $timezone;
    }
    
    
    
    
    
    
    protected $_language = null;
    
    public function establishLanguage(Sitengine_Controller_Request_Http $request, $param)
    {
        $value = $this->_establishValue($request, $param);
        $this->_language = $value;
        return $value;
    }
    
    
    public function getLanguage()
    {
        return $this->_language;
    }
    
    
    public function setLanguage($language)
    {
        $this->_language = $language;
    }
    
    
    
    
    
    
    protected $_translation = null;
    
    public function establishTranslation(Sitengine_Controller_Request_Http $request, $param)
    {
        $value = $this->_establishValue($request, $param);
        $this->_translation = $value;
        return $value;
    }
    
    
    public function getTranslation()
    {
        return $this->_translation;
    }
    
    
    
    
    
    
    protected $_itemsPerPage = null;
    
    public function establishItemsPerPage(Sitengine_Controller_Request_Http $request, $param)
    {
    	$value = $this->_establishValue($request, $param);
        $this->_itemsPerPage = $value;
        return $value;
    }
    
    
    public function getItemsPerPage()
    {
        return $this->_itemsPerPage;
    }
    
    
    
    
    
    
    protected $_debugMode = null;
    
    public function establishDebugMode(Sitengine_Controller_Request_Http $request, $param)
    {
        $value = $this->_establishValue($request, $param);
        $this->_debugMode = $value;
        return $value;
    }
    
    
    public function getDebugMode()
    {
    	return $this->_debugMode;
    }
    
    
    
    
    protected function _establishValue(Sitengine_Controller_Request_Http $request, $param)
    {
    	$value = (isset($this->_namespace->$param)) ? Sitengine_String::runtimeStripSlashes($this->_namespace->$param) : null;
		$value = ($request->get($param) !== null) ? $request->get($param) : $value;
        if(!$value) { unset($this->_namespace->$param); }
        else { $this->_namespace->$param = $value; }
        #print $param.' = '.$value.'<br />';
        return $value;
    }
    
}
?>