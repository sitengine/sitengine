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


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_ScaffoldSimple_Frontend_Front extends Sitengine_Controller_Front
{
	
	# controller names
	const CONTROLLER_ERROR = 'error';
    const CONTROLLER_LOGIN = 'login';
	const CONTROLLER_HOMIES = 'homies';
	
	# route names
    const ROUTE_LOGIN = 'login';
    const ROUTE_HOMIES = 'homies';
    
    
    
    
    protected $_permisoPackage = null;
    
    public function getPermisoPackage()
    {
    	if($this->_permisoPackage === null) {
    		$this->_permisoPackage = $this->_getPermisoPackageInstance();
    	}
    	return $this->_permisoPackage;
    }
    
    abstract protected function _getPermisoPackageInstance();
    
    
    
    
    
    
    protected $_scaffoldSimplePackage = null;
    
    public function getScaffoldSimplePackage()
    {
    	if($this->_scaffoldSimplePackage === null) {
    		$this->_scaffoldSimplePackage = $this->_getScaffoldSimplePackageInstance();
    	}
    	return $this->_scaffoldSimplePackage;
    }
    
    abstract protected function _getScaffoldSimplePackageInstance();
    
    
    
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/ScaffoldSimple/Frontend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_ScaffoldSimple_Frontend_Exception($message);
    	}
    	if($configName === null)
    	{
    		# set default
    		$configName = 'scaffoldSimple';
    	}
    	return parent::start($env, $configName);
    }
    
    
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(!isset($config->{$this->getConfigName()}->frontend->frontController))
    	{
    		require_once 'Sitengine/ScaffoldSimple/Frontend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_ScaffoldSimple_Frontend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->frontend->frontController;
    	
    	if(
    		isset($config->servicePath) &&
			isset($config->homiesController) &&
			isset($config->loginController) &&
			isset($config->errorController)
		)
		{
			$this->setServicePath($config->servicePath);
			
			$this->_controllers = array(
				self::CONTROLLER_HOMIES => $config->homiesController,
				self::CONTROLLER_LOGIN => $config->loginController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/ScaffoldSimple/Frontend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_ScaffoldSimple_Frontend_Exception($message);
		}
    }
    
    
    
    protected function _getRouterInstance()
    {
    	$path = $this->getServicePath();
    	
    	$routes = array(
			'default' => $this->_getRoute('*', self::CONTROLLER_HOMIES),
			self::ROUTE_LOGIN => $this->_getRoute("$path/login", self::CONTROLLER_LOGIN),
			self::ROUTE_HOMIES => $this->_getRoute("$path/homies", self::CONTROLLER_HOMIES)
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
		require_once 'Sitengine/Controller/Router/Route.php';
    	$route = new Sitengine_Controller_Router_Route($uri, $defaults);
    	return $route->setRepresentationParam(Sitengine_Env::PARAM_REPRESENTATION);
    }
    
    
    
    protected function _getErrorHandlerInstance()
    {
    	require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		$plugin = new Zend_Controller_Plugin_ErrorHandler();
		$plugin->setErrorHandlerController($this->getController(self::CONTROLLER_ERROR));
		return $plugin;
    }
    
}
?>