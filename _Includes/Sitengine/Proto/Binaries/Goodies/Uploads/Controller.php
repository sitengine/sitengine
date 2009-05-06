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


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_Proto_Binaries_Goodies_Uploads_Controller extends Sitengine_Controller_Action
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
			
			require_once 'Sitengine/Status.php';
			$this->_status = Sitengine_Status::getInstance();
			require_once 'Sitengine/Env/Preferences.php';
			$this->_preferences = Sitengine_Env_Preferences::getInstance();
			$this->_locale = $this->getEnv()->getLocaleInstance();
			$this->_permiso = $this->getFrontController()->getPermiso()->start($this->getDatabase());
			require_once 'Zend/Session/Namespace.php';
    		$this->_namespace = new Zend_Session_Namespace(get_class($this));
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Binaries/Goodies/Uploads/Exception.php';
            throw new Sitengine_Proto_Binaries_Goodies_Uploads_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_Proto_Binaries_Front &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		$this->_frontController = $invokeArgs['frontController'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/Proto/Binaries/Goodies/Uploads/Exception.php';
    		throw new Sitengine_Proto_Binaries_Goodies_Uploads_Exception('invalid invoke args');
    	}
    }
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	/*
    	$config = $config->protoFilesGoodiesUploadsController;
    	
    	if(
			isset($config->authorizedGroups)
		)
		{
			$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/Proto/Binaries/Goodies/Uploads/Exception.php';
			throw new Sitengine_Proto_Binaries_Goodies_Uploads_Exception('action controller config error');
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
				
				$this->getPreferences()->establishTranscript(
					$this->getRequest(),
					Sitengine_Env::PARAM_TRANSCRIPT
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
				
				#$this->getLocale()->setLocale($this->getPreferences()->getLanguage());
			}
		}
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Binaries/Goodies/Uploads/Exception.php';
            throw new Sitengine_Proto_Binaries_Goodies_Uploads_Exception('init error', $exception);
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
    		require_once 'Sitengine/Proto/Binaries/Goodies/Uploads/Exception.php';
    		throw new Sitengine_Proto_Binaries_Goodies_Uploads_Exception('trying to forward to a non-existing action handler');
    	}
    }
    
    
    
    public function __call($a, $b)
    {
    	$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
    	$method = $this->getRequest()->getIntendedMethod();
    	$action = null;
    	
    	switch($method) {
			case Sitengine_Env::METHOD_GET: $action = self::ACTION_BIN; break;
		}
    	if($action === null) {
    		require_once 'Sitengine/Proto/Binaries/Goodies/Uploads/Exception.php';
    		$exception = new Sitengine_Proto_Binaries_Goodies_Uploads_Exception(
    			"'$method' not supported on route '$route'",
    			Sitengine_Env::ERROR_NOT_IMPLEMENTED
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
			case Sitengine_Env::ERROR_FORBIDDEN:
				$handler = Sitengine_Error_Controller::ACTION_FORBIDDEN;
				break;
			case Sitengine_Env::ERROR_NOT_IMPLEMENTED:
				$handler = Sitengine_Error_Controller::ACTION_NOT_IMPLEMENTED;
				break;
			default:
				$handler = Sitengine_Error_Controller::ACTION_INTERNAL_SERVER_ERROR;
		}
			
		$pluginClass = 'Zend_Controller_Plugin_ErrorHandler';
		if($this->getFrontController()->hasPlugin($pluginClass))
		{
			$this->getFrontController()->getPlugin($pluginClass)->setErrorHandlerAction($handler);
		}
		return $exception;
    }
    
    
    
    public function getTempDir()
    {
    	$this->_start();
    	if(!$this->getFrontController()->getProtoPackage()->tempDirPerUser()) { return $this->getFrontController()->getProtoPackage()->getGoodyTempDir(); }
        else {
        	$userTempDir = $this->getFrontController()->getProtoPackage()->getGoodyTempDir().'/'.$this->getPermiso()->getAuth()->getId();
			if(!is_dir($userTempDir)) { mkdir($userTempDir, 0777); }
			return $userTempDir;
		}
    }
    
    
    
    protected function _binAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
				require_once 'Sitengine/Proto/Binaries/Goodies/Uploads/Exception.php';
				throw new Sitengine_Proto_Binaries_Goodies_Uploads_Exception(
					'unauthorized',
					Sitengine_Env::ERROR_FORBIDDEN
				);
			}
			
			$filename = $this->getRequest()->get(Sitengine_Env::PARAM_FILE);
            $path = $this->getTempDir().'/'.$filename;
            
            if(!is_readable($path)) {
            	require_once 'Sitengine/Proto/Binaries/Goodies/Uploads/Exception.php';
            	throw new Sitengine_Proto_Binaries_Goodies_Uploads_Exception('file can not be downloaded', Sitengine_Env::ERROR_NOT_FOUND);
            }
            if(preg_match('/\.(jpg|jpeg|pjpg)$/', $filename)) {
            	header('Content-type: image/jpeg');
            	header('Content-Disposition inline; filename="'.$filename.'"');
            }
            else if(preg_match('/\.(gif)$/', $filename)) {
            	header('Content-type: image/gif');
            	header('Content-Disposition inline; filename="'.$filename.'"');
            }
            else if(preg_match('/\.(png)$/', $filename)) {
            	header('Content-type: image/png');
            	header('Content-Disposition inline; filename="'.$filename.'"');
            }
            else {
            	header('Content-type: application/octet-stream');
            	header('Content-Disposition download; filename="'.$filename.'"');
            }
            $escapedProjectDir = preg_replace('/\//', '\/', $this->getEnv()->getMyProjectDir());
			$escapedProjectDir = preg_replace('/\./', '\.', $escapedProjectDir);
			$xsendPath = preg_replace('/'.$escapedProjectDir.'\//', '', $path);
			header("X-Sendfile: $xsendPath");
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