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
 * @package    Sitengine_Proto
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_Proto_Binaries_Front extends Sitengine_Controller_Front
{
	
    const CONTROLLER_GOODIES = 'goodies';
    const CONTROLLER_GOODIES_UPLOADS = 'goodiesUploads';
    const CONTROLLER_SHOULDIES = 'shouldies';
    const CONTROLLER_SHOULDIES_UPLOADS = 'shouldiesUploads';
    const CONTROLLER_COULDIES = 'couldies';
    const CONTROLLER_COULDIES_UPLOADS = 'couldiesUploads';
    const CONTROLLER_ERROR = 'error';
    
    
    protected $_protoPackage = null;
    
    public function getProtoPackage()
    {
    	if($this->_protoPackage === null) {
    		$this->_protoPackage = $this->_getProtoPackageInstance();
    	}
    	return $this->_protoPackage;
    }
    
    abstract protected function _getProtoPackageInstance();
    
    
    
    
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/Proto/Backend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_Proto_Backend_Exception($message);
    	}
    	if($configName === null)
    	{
    		# set default
    		$configName = 'proto';
    	}
    	return parent::start($env, $configName);
    }
    
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(!isset($config->{$this->getConfigName()}->binaries->frontController))
    	{
    		require_once 'Sitengine/Proto/Backend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_Proto_Backend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->binaries->frontController;
    	
    	if(
			isset($config->goodiesController) &&
			isset($config->goodiesUploadsController) &&
			isset($config->shouldiesController) &&
			isset($config->shouldiesUploadsController) &&
			isset($config->couldiesController) &&
			isset($config->couldiesUploadsController) &&
			isset($config->errorController)
		)
		{
			$this->_controllers = array(
				self::CONTROLLER_GOODIES => $config->goodiesController,
				self::CONTROLLER_GOODIES_UPLOADS => $config->goodiesUploadsController,
				self::CONTROLLER_SHOULDIES => $config->shouldiesController,
				self::CONTROLLER_SHOULDIES_UPLOADS => $config->shouldiesUploadsController,
				self::CONTROLLER_COULDIES => $config->couldiesController,
				self::CONTROLLER_COULDIES_UPLOADS => $config->couldiesUploadsController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/Proto/Backend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_Proto_Backend_Exception($message);
		}
    }
    
    
    /*
    protected function _getRouterInstance()
    {
    	require_once 'Zend/Controller/Router/Route.php';
    	
    	$routes = array(
			'default' => new Zend_Controller_Router_Route(
				'*',
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_GOODIES),
					Sitengine_Env::PARAM_ACTION => 'factory'
				)
			)
		);
		require_once 'Zend/Controller/Router/Rewrite.php';
		$router = new Zend_Controller_Router_Rewrite();
		$router->removeDefaultRoutes();
		$router->addRoutes($routes);
		return $router;
    }
    */
    
    
    
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