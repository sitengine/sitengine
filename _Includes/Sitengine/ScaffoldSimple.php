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
 * @package    Sitengine_ScaffoldSimple
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


abstract class Sitengine_ScaffoldSimple
{
    
    protected $_env = null;
    protected $_request = null;
    protected $_response = null;
    protected $_config = null;
	
	# properties loaded from config
	protected $_settingOne = null;
	
    
    
    public function __construct(
    	Sitengine_Env_Default $env,
    	Sitengine_Controller_Request_Http $request,
    	Zend_Controller_Response_Http $response,
    	Zend_Config $config
    )
    {
		$this->_env = $env;
		$this->_request = $request;
		$this->_response = $response;
		$this->_config = $config;
		$this->_mapConfig($config);
    }
    
    
    
    public function getRequest()
    {
    	return $this->_request;
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(
			isset($config->settingOne)
		)
		{
			$this->_settingOne = $config->settingOne;
			
		}
		else {
			require_once 'Sitengine/ScaffoldSimple/Exception.php';
        	throw new Sitengine_ScaffoldSimple_Exception('package config error');
       	}
    }
    
    
    
    public function getSettingOne()
    {
    	return $this->_settingOne;
    }
    
    
    
    protected $_database = null;
    
    
    public function start(Zend_Db_Adapter_Abstract $database)
    {
    	$this->_database = $database;
    	return $this;
    }
    
    
}
?>