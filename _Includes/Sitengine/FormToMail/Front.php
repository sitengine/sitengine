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


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_FormToMail_Front extends Sitengine_Controller_Front
{
	
    const CONTROLLER_INDEX = 'index';
    const CONTROLLER_CONFIRM = 'confirm';
    const CONTROLLER_LOGIN = 'login';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_INDEX = 'index';
    const ROUTE_CONFIRM = 'confirm';
    const ROUTE_LOGIN = 'login';
    
    
    
    protected $_formToMailPackage = null;
    
    public function getFormToMailPackage()
    {
    	if($this->_formToMailPackage === null) {
    		$this->_formToMailPackage = $this->_getFormToMailPackageInstance();
    	}
    	return $this->_formToMailPackage;
    }
    
    abstract protected function _getFormToMailPackageInstance();
    
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/FormToMail/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_FormToMail_Exception($message);
    	}
    	return parent::start($env, $configName);
    }
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	$config = $config->frontController;
    	
    	if(
			isset($config->indexController) &&
			isset($config->loginController) &&
			isset($config->errorController)
		)
		{
			$this->_controllers = array(
				self::CONTROLLER_INDEX => $config->indexController,
				self::CONTROLLER_LOGIN => $config->loginController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/FormToMail/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_FormToMail_Default_Backend_Exception($message);
		}
    }
    
    
    
    protected function _getRouterInstance()
    {
    	$routes = array(
			'default' => $this->_getRoute('*', self::CONTROLLER_INDEX),
			self::ROUTE_INDEX => $this->_getRoute('form-to-mail', self::CONTROLLER_INDEX),
			self::ROUTE_CONFIRM => $this->_getRoute('form-to-mail/confirm', self::CONTROLLER_INDEX),
			self::ROUTE_LOGIN => $this->_getRoute('form-to-mail/login', self::CONTROLLER_LOGIN)
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
    
    
    
    protected $_permisoPackage = null;
    
    public function getPermisoPackage()
    {
    	if($this->_permisoPackage === null) {
    		$this->_permisoPackage = $this->_getPermisoPackageInstance();
    	}
    	return $this->_permisoPackage;
    }
    
    abstract protected function _getPermisoPackageInstance();
    
    
    
    protected function _getErrorHandlerInstance()
    {
    	require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		$plugin = new Zend_Controller_Plugin_ErrorHandler();
		$plugin->setErrorHandlerController($this->getController(self::CONTROLLER_ERROR));
		return $plugin;
    }
    
}
?>