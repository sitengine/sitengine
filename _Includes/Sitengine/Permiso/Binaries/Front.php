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
 * @package    Sitengine_Permiso
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_Permiso_Binaries_Front extends Sitengine_Controller_Front
{
	
    const CONTROLLER_USERS = 'users';
    const CONTROLLER_ERROR = 'error';
    
    
    protected $_permiso = null;
    
    public function getPermiso()
    {
    	if($this->_permiso === null) {
    		$this->_permiso = $this->_getPermisoInstance();
    	}
    	return $this->_permiso;
    }
    
    abstract protected function _getPermisoInstance();
    
    
    
    
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/Permiso/Backend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_Permiso_Backend_Exception($message);
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
    	if(!isset($config->{$this->getConfigName()}->binaries->frontController))
    	{
    		require_once 'Sitengine/Permiso/Backend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_Permiso_Backend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->binaries->frontController;
    	
    	if(
			isset($config->usersController) &&
			isset($config->errorController)
		)
		{
			$this->_controllers = array(
				self::CONTROLLER_USERS => $config->usersController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/Permiso/Backend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_Permiso_Backend_Exception($message);
		}
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