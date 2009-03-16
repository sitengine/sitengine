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
 * @package    Sitengine_CampaignMonitor
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


abstract class Sitengine_CampaignMonitor
{
    
    protected $_env = null;
    protected $_request = null;
    protected $_response = null;
    protected $_config = null;
    
    protected $_ownerGroup = null;
    protected $_authorizedGroups = null;
    
    
    
    
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
    
    
    
    public function getEnv()
    {
    	return $this->_env;
    }
    
    
    
    public function getRequest()
    {
    	return $this->_request;
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(
    		isset($config->ownerGroup) &&
    		isset($config->authorizedGroups)
		)
		{
    		$this->_ownerGroup = $config->ownerGroup;
    		$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/CampaignMonitor/Exception.php';
        	throw new Sitengine_CampaignMonitor_Exception('package config error');
       	}
    }
    
    
    public function getOwnerGroup() { return $this->_ownerGroup; }
    public function getAuthorizedGroups() { return $this->_authorizedGroups; }
    
}
?>