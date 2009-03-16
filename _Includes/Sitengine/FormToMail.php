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
 * @package    Sitengine_FormToMail
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


abstract class Sitengine_FormToMail
{
	
    protected $_env = null;
    protected $_request = null;
    protected $_response = null;
    protected $_config = null;
    
    protected $_senderEmail = null;
    protected $_senderName = null;
    protected $_authorizedGroups = array();
    
    
	public function getAuthorizedGroups() { return $this->_authorizedGroups; }
    public function getSenderEmail() { return $this->_senderEmail; }
    public function getSenderName() { return $this->_senderName; }
    
    
    
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
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(
    		isset($config->senderEmail) &&
    		isset($config->senderName) &&
    		isset($config->authorizedGroups)
    	)
		{
			$this->_senderEmail = $config->senderEmail;
			$this->_senderName = $config->senderName;
			$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/FormToMail/Default/Exception.php';
			throw new Sitengine_FormToMail_Default_Exception('package config error');
		}
    }
}
?>