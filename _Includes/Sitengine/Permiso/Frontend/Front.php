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
 * @package    Sitengine_Permiso_Frontend_User
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_Permiso_Frontend_Front extends Sitengine_Controller_Front
{
	
    const CONTROLLER_USER = 'user';
    const CONTROLLER_LOGIN = 'login';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_USER = 'user';
    const ROUTE_USER_NEW = 'userNew';
    const ROUTE_LOGIN = 'login';
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/Permiso/Frontend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_Permiso_Frontend_Exception($message);
    	}
    	if($configName === null)
    	{
    		# set default
    		$configName = 'permiso';
    	}
    	return parent::start($env, $configName);
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(!isset($config->{$this->getConfigName()}->frontend->frontController))
    	{
    		require_once 'Sitengine/Permiso/Frontend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_Permiso_Frontend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->frontend->frontController;
    	
    	if(
    		isset($config->servicePath) &&
			isset($config->userController) &&
			isset($config->loginController) &&
			isset($config->errorController)
		)
		{
			$this->setServicePath($config->servicePath);
			
			$this->_controllers = array(
				self::CONTROLLER_USER => $config->userController,
				self::CONTROLLER_LOGIN => $config->loginController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/Permiso/Frontend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_Permiso_Frontend_Exception($message);
		}
    }
    
    
    
    protected function _getRouterInstance()
    {
    	$path = $this->getServicePath();
    	
    	$routes = array(
			'default'				=> $this->_getRoute('*', self::CONTROLLER_USER),
			self::ROUTE_USER		=> $this->_getRoute("$path/user", self::CONTROLLER_USER),
			self::ROUTE_USER_NEW	=> $this->_getRoute("$path/join", self::CONTROLLER_USER),
			self::ROUTE_LOGIN		=> $this->_getRoute("$path/login", self::CONTROLLER_LOGIN)
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
			Sitengine_Env::PARAM_ACTION => 'restMapper'
		);
		require_once 'Zend/Controller/Router/Route.php';
    	return new Zend_Controller_Router_Route($uri, $defaults);
    }
    
    
    
    protected $_permiso = null;
    
    public function getPermiso()
    {
    	if($this->_permiso === null) {
    		$this->_permiso = $this->_getPermisoInstance();
    	}
    	return $this->_permiso;
    }
    
    abstract protected function _getPermisoInstance();
    
    
    
    protected function _getErrorHandlerInstance()
    {
    	require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		$plugin = new Zend_Controller_Plugin_ErrorHandler();
		$plugin->setErrorHandlerController($this->getController(self::CONTROLLER_ERROR));
		return $plugin;
    }
    
}
?>