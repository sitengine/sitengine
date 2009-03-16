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
 * @package    Sitengine_Newsletter
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_Newsletter_Frontend_Front extends Sitengine_Controller_Front
{
	
	const CONTROLLER_CAMPAIGNS = 'campaigns';
	const CONTROLLER_SUBSCRIPTIONS = 'subscriptions';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_CAMPAIGNS = 'campaigns';
    const ROUTE_CAMPAIGNS_SHARP = 'campaignsSharp';
    const ROUTE_SUBSCRIPTIONS_SUBSCRIBE = 'subscriptionsSubscribe';
    const ROUTE_SUBSCRIPTIONS_UNSUBSCRIBE = 'subscriptionsUnsubscribe';
    const ROUTE_SUBSCRIPTIONS_CONFIRM_OPTIN = 'subscriptionsConfirmOptin';
    const ROUTE_SUBSCRIPTIONS_CONFIRM_FINAL = 'subscriptionsConfirmFinal';
    const ROUTE_SUBSCRIPTIONS_CONFIRM_UNSUBSCRIBE = 'subscriptionsconfirmUnsubscribe';
    
    
    
    protected $_newsletterPackage = null;
    
    public function getNewsletterPackage()
    {
    	if($this->_newsletterPackage === null) {
    		$this->_newsletterPackage = $this->_getNewsletterPackageInstance();
    	}
    	return $this->_newsletterPackage;
    }
    
    abstract protected function _getNewsletterPackageInstance();
    
    
    
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/Newsletter/Frontend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_Newsletter_Frontend_Exception($message);
    	}
    	if($configName === null)
    	{
    		# set default
    		$configName = 'newsletter';
    	}
    	return parent::start($env, $configName);
    }
    
    
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(!isset($config->{$this->getConfigName()}->frontend->frontController))
    	{
    		require_once 'Sitengine/Newsletter/Frontend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_Newsletter_Frontend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->frontend->frontController;
    	
    	if(
    		isset($config->servicePath) &&
			isset($config->campaignsController) &&
			isset($config->subscriptionsController) &&
			isset($config->errorController)
		)
		{
			$this->setServicePath($config->servicePath);
			$this->_controllers = array(
				self::CONTROLLER_CAMPAIGNS => $config->campaignsController,
				self::CONTROLLER_SUBSCRIPTIONS => $config->subscriptionsController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/Newsletter/Frontend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_Newsletter_Frontend_Exception($message);
		}
    }
    
    
    
    protected function _getRouterInstance()
    {
    	$path = $this->getServicePath();
    	
    	$routes = array(
			'default' => $this->_getRoute('*', self::CONTROLLER_CAMPAIGNS),
			self::ROUTE_CAMPAIGNS							=> $this->_getRoute("$path/campaigns", self::CONTROLLER_CAMPAIGNS),
			self::ROUTE_CAMPAIGNS_SHARP						=> $this->_getRoute("$path/campaigns/:id", self::CONTROLLER_CAMPAIGNS),
			self::ROUTE_SUBSCRIPTIONS_SUBSCRIBE				=> $this->_getRoute("$path/subscriptions", self::CONTROLLER_SUBSCRIPTIONS),
			self::ROUTE_SUBSCRIPTIONS_UNSUBSCRIBE			=> $this->_getRoute("$path/subscriptions/unsubscribe", self::CONTROLLER_SUBSCRIPTIONS),
			self::ROUTE_SUBSCRIPTIONS_CONFIRM_OPTIN			=> $this->_getRoute("$path/subscriptions/confirm/optin", self::CONTROLLER_SUBSCRIPTIONS),
			self::ROUTE_SUBSCRIPTIONS_CONFIRM_FINAL			=> $this->_getRoute("$path/subscriptions/confirm/final", self::CONTROLLER_SUBSCRIPTIONS),
			self::ROUTE_SUBSCRIPTIONS_CONFIRM_UNSUBSCRIBE	=> $this->_getRoute("$path/subscriptions/confirm/unsubscribe", self::CONTROLLER_SUBSCRIPTIONS),
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