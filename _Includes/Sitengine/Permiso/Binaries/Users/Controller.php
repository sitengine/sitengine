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


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_Permiso_Binaries_Users_Controller extends Sitengine_Controller_Action
{
    
    const ACTION_BIN = '_bin';
    
    protected $_started = false;
    protected $_config = null;
    protected $_env = null;
    protected $_logger = null;
    protected $_database = null;
    protected $_status = null;
    protected $_preferences = null;
    protected $_locale = null;
    protected $_permiso = null;
    protected $_namespace = null;
    
    
    public function getEnv() { return $this->_env; }
    public function getLogger() { return $this->_logger; }
    public function getDatabase() { return $this->_database; }
    public function getStatus() { return $this->_status; }
    public function getPreferences() { return $this->_preferences; }
    public function getLocale() { return $this->_locale; }
    public function getPermiso() { return $this->_permiso; }
    public function getNamespace() { return $this->_namespace; }
    public function getTranslate() { return $this->_translate; }
    
    
			
			
    public function __construct(
    	Sitengine_Controller_Request_Http $request,
    	Zend_Controller_Response_Http $response,
    	array $invokeArgs
    )
    {
        try {
        	parent::__construct($request, $response, $invokeArgs);
        	$this->_mapInvokeArgs($invokeArgs);
        	$this->_mapConfig($this->_config);
        	
        	$this->_logger = $this->getEnv()->getLoggerInstance(
        		$this->getEnv()->getMyLogsDir(),
        		gmdate('Ymd').'-sitengine.log',
        		$this->getEnv()->getLogFilterPriority(),
        		get_class($this)
        	);
        	
			$this->_database = $this->getEnv()->getDatabaseInstance(
				'Pdo_Mysql',
				$this->getEnv()->getDatabaseConfig('default'),
				$this->getEnv()->getDebugControl()
			);
			
			$this->getEnv()->startSession($this->getDatabase());
			$this->getFrontController()->getPermisoPackage()->start($this->getDatabase());
			
			require_once 'Sitengine/Status.php';
			$this->_status = Sitengine_Status::getInstance();
			require_once 'Sitengine/Env/Preferences.php';
			$this->_preferences = Sitengine_Env_Preferences::getInstance();
			$this->_locale = $this->getEnv()->getLocaleInstance();
			$this->_permiso = $this->getFrontController()->getPermisoPackage()->start($this->getDatabase());
			require_once 'Zend/Session/Namespace.php';
    		$this->_namespace = new Zend_Session_Namespace(get_class($this));
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Binaries/Users/Exception.php';
            throw new Sitengine_Permiso_Binaries_Users_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_Permiso_Binaries_Front &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		$this->_frontController = $invokeArgs['frontController'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/Permiso/Binaries/Users/Exception.php';
    		throw new Sitengine_Permiso_Binaries_Users_Exception('invalid invoke args');
    	}
    }
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	/*
    	$config = $config->permisoFilesUsersController;
    	
    	if(
			isset($config->authorizedGroups)
		)
		{
			$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/Permiso/Binaries/Users/Exception.php';
			throw new Sitengine_Permiso_Binaries_Users_Exception('action controller config error');
		}
		*/
    }
    
    
    
    
    protected function _start()
    {
    	try {
			if(!$this->_started)
			{
				$this->_started = true;
				#$this->getDatabase()->query('SET NAMES "utf8"');
				
				if($this->getRequest()->get(Sitengine_Env::PARAM_LOGOUT))
				{
					$this->getPermiso()->getAuth()->clearIdentity();
				}
				
				if($this->getPermiso()->getAuth()->hasIdentity())
				{
					$this->getPermiso()->getAuth()->extendValidity();
				}
				
				$this->getPreferences()->establishLanguage(
					$this->getRequest(),
					Sitengine_Env::PARAM_LANGUAGE
				);
				
				$this->getPreferences()->establishTranslation(
					$this->getRequest(),
					Sitengine_Env::PARAM_TRANSLATION
				);
				
				$this->getPreferences()->establishItemsPerPage(
					$this->getRequest(),
					Sitengine_Env::PARAM_IPP
				);
				
				$this->getPreferences()->establishTimezone(
					$this->getRequest(),
					Sitengine_Env::PARAM_TIMEZONE
				);
				
				$this->getPreferences()->establishDebugMode(
					$this->getRequest(),
					Sitengine_Env::PARAM_DBG
				);
				
				if($this->getEnv()->getDebugControl()) {
					require_once 'Sitengine/Debug.php';
					Sitengine_Debug::action($this->getPreferences()->getDebugMode());
				}
				
				$this->getLocale()->setLocale($this->getPreferences()->getLanguage());
			}
		}
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Binaries/Users/Exception.php';
            throw new Sitengine_Permiso_Binaries_Users_Exception('init error', $exception);
        }
    }
    
    
    
    protected function _goToAction($action)
    {
    	$handler = $action.'Action';
    	if(is_callable(array($this, $handler))) {
    		$this->getRequest()->setActionName($action);
    		call_user_func(array($this, $handler));
    	}
    	else {
    		require_once 'Sitengine/Permiso/Binaries/Users/Exception.php';
    		throw new Sitengine_Permiso_Binaries_Users_Exception('trying to forward to a non-existing action handler');
    	}
    }
    
    
    
    public function __call($a, $b)
    {
    	$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
    	$method = $this->getRequest()->getMethod();
    	$action = null;
    	
    	switch($method) {
			case Sitengine_Env::METHOD_GET: $action = self::ACTION_BIN; break;
		}
    	if($action === null) {
    		require_once 'Sitengine/Permiso/Binaries/Users/Exception.php';
    		$exception = new Sitengine_Permiso_Binaries_Users_Exception(
    			'method not supported',
    			Sitengine_Env::ERROR_NOT_SUPPORTED
    		);
    		throw $this->_prepareErrorHandler($exception);
    	}
    	return $this->_goToAction($action);
    }
    
    
    
    protected function _prepareErrorHandler(Exception $exception)
    {
    	if($this->getPreferences() !== null) {
    		$this->getRequest()->setParam(
    			Sitengine_Env::PARAM_LANGUAGE,
    			$this->getPreferences()->getLanguage()
    		);
    	}
    	
		require_once 'Sitengine/Error/Controller.php';
		switch($exception->getCode())
		{
			case Sitengine_Env::ERROR_NOT_FOUND:
				$handler = Sitengine_Error_Controller::ACTION_NOT_FOUND;
				break;
			case Sitengine_Env::ERROR_BAD_REQUEST:
				$handler = Sitengine_Error_Controller::ACTION_BAD_REQUEST;
				break;
			case Sitengine_Env::ERROR_UNAUTHORIZED:
				$handler = Sitengine_Error_Controller::ACTION_UNAUTHORIZED;
				break;
			case Sitengine_Env::ERROR_NOT_SUPPORTED:
				$handler = Sitengine_Error_Controller::ACTION_NOT_SUPPORTED;
				break;
			default:
				$handler = Sitengine_Error_Controller::ACTION_INTERNAL;
		}
			
		$pluginClass = 'Zend_Controller_Plugin_ErrorHandler';
		if($this->getFrontController()->hasPlugin($pluginClass))
		{
			$plugin = $this->getFrontController()->getPlugin($pluginClass);
			$plugin->setErrorHandlerAction($handler);
		}
		return $exception;
    }
    
    
    
    protected function _binAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getPermisoPackage()->getAuthorizedGroups())) {
				require_once 'Sitengine/Permiso/Binaries/Users/Exception.php';
				throw new Sitengine_Permiso_Binaries_Users_Exception(
					'unauthorized',
					Sitengine_Env::ERROR_UNAUTHORIZED
				);
			}
			
    		$id = $this->getRequest()->get(Sitengine_Env::PARAM_ID);
    		$fileId = $this->getRequest()->get(Sitengine_Env::PARAM_FILE);
    		
            $table = $this->getFrontController()->getPermisoPackage()->getUsersTable();
            $select = $table->select()->where('id = ?', $id);
        	$row = $table->fetchRow($select);
        	if($row === null)
        	{
        		require_once 'Sitengine/Permiso/Binaries/Users/Exception.php';
        		throw new Sitengine_Permiso_Binaries_Users_Exception(
					'resource not found',
					Sitengine_Env::ERROR_NOT_FOUND
				);
			}
			$file = $table->complementRow($row);
			if(!isset($file[$fileId.'Name']) || $file[$fileId.'Name'] == '') {
				require_once 'Sitengine/Permiso/Binaries/Users/Exception.php';
				throw new Sitengine_Permiso_Binaries_Users_Exception(
					'resource not found',
					Sitengine_Env::ERROR_NOT_FOUND
				);
			}
			#Sitengine_Debug::print_r($file);
			
			$escapedProjectDir = preg_replace('/\//', '\/', $this->getEnv()->getMyProjectDir());
			$escapedProjectDir = preg_replace('/\./', '\.', $escapedProjectDir);
			$xsendPath = preg_replace('/'.$escapedProjectDir.'\//', '', $file[$fileId.'Path']);
			$this->getResponse()->setHeader('X-Sendfile', $xsendPath);
			$this->getResponse()->setHeader('Content-Type', $file[$fileId.'Mime']);
			#$this->getResponse()->setHeader('Content-Type', 'application/octet-stream');
			$this->getResponse()->setHeader('Content-length', $file[$fileId.'Size']);
			$this->getResponse()->setHeader('Content-Disposition', 'inline; filename='.$file[$fileId.'Name']);
			$this->getResponse()->sendResponse();
		}
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _getDebugDump(array $data)
    {
    	$dump = '';
    	
		if($this->getEnv()->getDebugControl())
		{
			ob_start();
			$debugMode = $this->getPreferences()->getDebugMode();
			
			if($debugMode == 'queries') {
				require_once 'Sitengine/Db/Debug.php';
				Sitengine_Db_Debug::profiler($this->getDatabase());
			}
			else if($debugMode=='templateData') {
				print '<hr /><h1>TEMPLATE DATA</h1><hr />';
				Sitengine_Debug::print_r($data);
			}
			else {
				print Sitengine_Debug::info($debugMode);
			}
			$dump = ob_get_contents();
			ob_end_clean();
		}
		return $dump;
    }
    
}
?>