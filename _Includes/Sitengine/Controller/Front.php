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
 * @package    Sitengine
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Zend/Controller/Front.php';


abstract class Sitengine_Controller_Front extends Zend_Controller_Front
{
	
	protected $_started = false;
    protected $_env = null;
    protected $_controllers = array();
    protected $_config = array();
    protected $_servicePath = '';
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	try {
			if(!$this->_started)
			{
				$this->_started = true;
				$this->_env = $env;
				$this->_config = $this->_loadConfig();
				$this->_configName = $configName;
				$this->_mapConfig($this->_config);
				
				$this->setParam('useDefaultControllerAlways', false);
				$this->setParam('disableOutputBuffering', true); # no buffering in Dispatcher::dispatch()
				$this->setParam('noViewRenderer', true);
				$this->setParam('noErrorHandler', false);
				$this->returnResponse(true);
				$this->throwExceptions(false);
				
				require_once 'Sitengine/Controller/Request/Http.php';
				$this->_request = new Sitengine_Controller_Request_Http();
				$this->_request
					->setModuleKey(Sitengine_Env::PARAM_MODULE)
					->setControllerKey(Sitengine_Env::PARAM_CONTROLLER)
					->setActionKey(Sitengine_Env::PARAM_ACTION)
				;
				
				#print 'setRequestUri: '.$this->_request->getRequestUri().'<br />';
				#print 'getBaseUrl: '.$this->_request->getBaseUrl().'<br />';
				#print 'getBasePath: '.$this->_request->getBasePath().'<br />';
				#print 'PathInfo: '.$this->_request->getPathInfo().'<br />';
				
				
				require_once 'Zend/Controller/Response/Http.php';
				$this->_response = new Zend_Controller_Response_Http();
				
				if($this->_env->getDebugControl()) {
					$this->throwExceptions(true);
					$this->_response->renderExceptions(true);
				}
				
				$this->_dispatcher = $this->_getDispatcherInstance();
				$this->_dispatcher->setControllerDirectory($this->_env->getMyIncludesDir());
				$this->_dispatcher->setFrontController($this);
				$this->_dispatcher->setResponse($this->_response);
				$this->_dispatcher->setParams($this->_invokeParams);
				
				$this->_router = $this->_getRouterInstance();
				$this->_router->setFrontController($this);
				
				$this->registerPlugin($this->_getErrorHandlerInstance());
				
				# required in action controller constructors
				$this->setParam('frontController', $this);
				$this->setParam('env', $this->_env);
				$this->setParam('config', $this->_config);
				
				# set the rewrite base
				$this->setBaseUrl($this->_request->getBasePath());
			}
			return $this;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Controller/Exception.php';
			$message = 'front controller start error';
			throw new Sitengine_Controller_Exception($message, $exception);
		}
    }
    
    
    protected function _loadConfig()
    {
    	require_once 'Zend/Config.php';
		return new Zend_Config(array());
    }
    
    
    protected function _mapConfig(Zend_Config $config) {}
    
    
    
    protected function _getDispatcherInstance()
    {
		require_once 'Sitengine/Controller/Dispatcher/Standard.php';
		return new Sitengine_Controller_Dispatcher_Standard();
    }
    
    
    
    protected function _getRouterInstance()
    {
		require_once 'Zend/Controller/Router/Rewrite.php';
		return new Zend_Controller_Router_Rewrite();
    }
    
    
	
	protected function _getErrorHandlerInstance()
    {
    	require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		return new Zend_Controller_Plugin_ErrorHandler();
    }
    
    
    /*
    protected function _getPackageInstance()
    {
    	return null;
    }
    */
    
    
    public function getControllers()
    {
    	return $this->_controllers;
    }
    
    
    
    public function getController($handle)
    {
    	if(isset($this->_controllers[$handle])) { return $this->_controllers[$handle]; }
    	else {
    		require_once 'Sitengine/Controller/Exception.php';
			$message = 'request for controller name non-existing handle: '.$handle;
			throw new Sitengine_Controller_Exception($message);
    	}
    }
    
    
    
    public function getEnv()
    {
    	return $this->_env;
    }
    
    
    public function getConfig()
    {
    	return $this->_config;
    }
    
    
    public function getConfigName()
    {
    	return $this->_configName;
    }
    
    
    public function getServicePath()
    {
    	return $this->_servicePath;
    }
    
    
    public function setServicePath($path)
    {
    	$this->_servicePath = $path;
    	return $this;
    }
    
    
    public function getControllerInstance($handle)
    {
    	try {
    		if($this->_started) {
				$class = $this->getController($handle);
				include_once str_replace('_', DIRECTORY_SEPARATOR, $class.'.php');
				return new $class($this->_request, $this->_response, $this->_invokeParams);
    		}
    		else {
    			require_once 'Sitengine/Controller/Exception.php';
    			$message = 'start() must be called before calling getControllerInstance()';
    			throw new Sitengine_Controller_Exception($message);
    		}
		}
		catch (Exception $exception)
    	{
    		require_once 'Sitengine/Controller/Exception.php';
			$message = 'could not instantiate controller';
			throw new Sitengine_Controller_Exception($message);
		}
    }
    
    
	
	public function dispatch(
		Zend_Controller_Request_Abstract $request = null,
		Zend_Controller_Response_Abstract $response = null
	)
    {
    	try {
    		if($this->_started) {
    			return parent::dispatch($request, $response);
    		}
    		else {
    			require_once 'Sitengine/Controller/Exception.php';
    			$message = 'start() must be called before calling dispatch()';
    			throw new Sitengine_Controller_Exception($message);
    		}
    	}
    	catch (Exception $exception)
    	{
    		$message = 'A System Error has occured. Please check back later';
    		if($this->_response instanceof Zend_Controller_Response_Http)
    		{
				$this->_response->setException($exception);
				$this->_response->setHttpResponseCode(500);
				$this->_response->sendResponse();
				print $message;
				return $this->_response;
			}
			else {
				print $message;
				return null;
			}
		}
    }
}

?>