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


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_CampaignMonitor_Frontend_Front extends Sitengine_Controller_Front
{
	
	const CONTROLLER_SUBSCRIBERS = 'subscribers';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_SUBSCRIBERS_SUBSCRIBE = 'subscribersSubscribe';
    const ROUTE_SUBSCRIBERS_UNSUBSCRIBE = 'subscribersUnsubscribe';
    const ROUTE_SUBSCRIBERS_CONFIRM_OPTIN = 'subscribersConfirmOptin';
    const ROUTE_SUBSCRIBERS_CONFIRM_FINAL = 'subscribersConfirmFinal';
    const ROUTE_SUBSCRIBERS_CONFIRM_UNSUBSCRIBE = 'subscribersconfirmUnsubscribe';
    
    
    
    protected $_campaignMonitorPackage = null;
    
    public function getCampaignMonitorPackage()
    {
    	if($this->_campaignMonitorPackage === null) {
    		$this->_campaignMonitorPackage = $this->_getCampaignMonitorPackageInstance();
    	}
    	return $this->_campaignMonitorPackage;
    }
    
    abstract protected function _getCampaignMonitorPackageInstance();
    
    
    
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/CampaignMonitor/Frontend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_CampaignMonitor_Frontend_Exception($message);
    	}
    	if($configName === null)
    	{
    		# set default
    		$configName = 'campaignMonitor';
    	}
    	return parent::start($env, $configName);
    }
    
    
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(!isset($config->{$this->getConfigName()}->frontend->frontController))
    	{
    		require_once 'Sitengine/CampaignMonitor/Frontend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_CampaignMonitor_Frontend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->frontend->frontController;
    	
    	if(
    		isset($config->servicePath) &&
			isset($config->subscribersController) &&
			isset($config->errorController)
		)
		{
			$this->setServicePath($config->servicePath);
			$this->_controllers = array(
				self::CONTROLLER_SUBSCRIBERS => $config->subscribersController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/CampaignMonitor/Frontend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_CampaignMonitor_Frontend_Exception($message);
		}
    }
    
    
    
    protected function _getRouterInstance()
    {
    	$path = $this->getServicePath();
    	
    	$routes = array(
			'default' => $this->_getRoute('*', self::CONTROLLER_SUBSCRIBERS),
			self::ROUTE_SUBSCRIBERS_SUBSCRIBE			=> $this->_getRoute("$path/subscribers", self::CONTROLLER_SUBSCRIBERS),
			self::ROUTE_SUBSCRIBERS_UNSUBSCRIBE			=> $this->_getRoute("$path/subscribers/unsubscribe", self::CONTROLLER_SUBSCRIBERS),
			self::ROUTE_SUBSCRIBERS_CONFIRM_OPTIN		=> $this->_getRoute("$path/subscribers/confirm/optin", self::CONTROLLER_SUBSCRIBERS),
			self::ROUTE_SUBSCRIBERS_CONFIRM_FINAL		=> $this->_getRoute("$path/subscribers/confirm/final", self::CONTROLLER_SUBSCRIBERS),
			self::ROUTE_SUBSCRIBERS_CONFIRM_UNSUBSCRIBE	=> $this->_getRoute("$path/subscribers/confirm/unsubscribe", self::CONTROLLER_SUBSCRIBERS),
		);
		require_once 'Zend/Controller/Router/Rewrite.php';
		$router = new Zend_Controller_Router_Rewrite();
		$router->removeDefaultRoutes();
		$router->addRoutes($routes);
		return $router;
    }
    
    
    
    protected function _getRoute($uri, $controller)
    {
    	$defaults = array(
			Sitengine_Env::PARAM_CONTROLLER => $this->getController($controller),
			Sitengine_Env::PARAM_ACTION => 'factory'
		);
		require_once 'Zend/Controller/Router/Route.php';
    	return new Zend_Controller_Router_Route($uri, $defaults);
    }
    
    
    
    protected function _getErrorHandlerInstance()
    {
    	require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		$plugin = new Zend_Controller_Plugin_ErrorHandler();
		$plugin->setErrorHandlerController($this->getController(self::CONTROLLER_ERROR));
		return $plugin;
    }
    
    
    
    
    
    protected $_permisoPackage = null;
    
    public function getPermisoPackage()
    {
    	if($this->_permisoPackage === null) {
    		$this->_permisoPackage = $this->_getPermisoPackageInstance();
    	}
    	return $this->_permisoPackage;
    }
    
    abstract protected function _getPermisoPackageInstance();
    

}
?>