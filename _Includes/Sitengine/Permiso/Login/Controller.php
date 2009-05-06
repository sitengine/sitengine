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


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_Permiso_Login_Controller extends Sitengine_Controller_Action
{
    
	#const ACTION_INDEX = 'index';
	#const ERROR_BAD_REQUEST = -1;
    
	
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
    protected $_translate = null;
    protected $_templateIndexView = null;
    
    
    public function getEnv() { return $this->_env; }
    public function getLogger() { return $this->_logger; }
    public function getDatabase() { return $this->_database; }
    public function getStatus() { return $this->_status; }
    public function getPreferences() { return $this->_preferences; }
    public function getLocale() { return $this->_locale; }
    public function getPermiso() { return $this->_permiso; }
    public function getNamespace() { return $this->_namespace; }
    public function getTranslate() { return $this->_translate; }
    
    
    
    # objects that are not initialized on controller init
    private $_viewHelper = null;
    
    public function getViewHelper()
    {
    	if($this->_viewHelper === null) {
    		$this->_viewHelper = $this->_getViewHelperInstance();
    	}
    	return $this->_viewHelper;
    }
    
    abstract protected function _getViewHelperInstance();
    
    
	
	
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
        	$this->_translate = $this->_getTranslateInstance();
        	require_once 'Zend/Session/Namespace.php';
    		$this->_namespace = new Zend_Session_Namespace(get_class($this));
        	$this->_setTemplates();
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Permiso/Login/Exception.php';
            throw new Sitengine_Permiso_Login_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _setTemplates()
    {
        $this->_templateIndexView = $this->getEnv()->getIncludesDir().'/Sitengine/Permiso/Login/_Templates/IndexView.html';
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_Controller_Front &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		#$this->_package = $invokeArgs['package'];
    		$this->_frontController = $invokeArgs['frontController'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/Permiso/Login/Exception.php';
    		throw new Sitengine_Permiso_Login_Exception('invalid invoke args');
    	}
    }
    
    
    protected function _mapConfig(Zend_Config $config) {}
    
    
    abstract protected function _getIndexViewInstance();
    
    
    
    
    protected function _getTranslateInstance()
    {
    	require_once 'Sitengine/Translate.php';
		$translate = new Sitengine_Translate(
			Sitengine_Translate::AN_XML,
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/global.xml',
			Sitengine_Env::LANGUAGE_EN
		);
		
		$en = array(
			$this->getEnv()->getIncludesDir().'/Sitengine/Permiso/Login/_Dictionary/en.xml'
		);
		
		$de = array(
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/global.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/Permiso/Login/_Dictionary/de.xml'
		);
		
		$translate->addMergeTranslation($en, Sitengine_Env::LANGUAGE_EN);
		$translate->addMergeTranslation($de, Sitengine_Env::LANGUAGE_DE);
		return $translate;
    }
    
    
    abstract protected function _getSelfUri();
    
    
    protected function _getDefaultTarget()
    {
    	return '/';
    }
    
    
    protected function _start()
    {
    	try {
    		$errorOrgNotFound = 'organization not found';
    		
			if(!$this->_started)
			{
				$this->_started = true;
				
				if($this->getRequest()->getPost(Sitengine_Env::PARAM_LOGINUSER))
				{
					$this->getPermiso()->getAuthAdapter()
						->setIdentity($this->getRequest()->getPost(Sitengine_Env::PARAM_LOGINUSER))
						->setCredential(md5($this->getRequest()->getPost(Sitengine_Env::PARAM_LOGINPASS)))
					;
					
					$result = $this->getPermiso()->getAuth()->authenticate($this->getPermiso()->getAuthAdapter());
				}
				
				$this->getRequest()->setParam(Sitengine_Env::PARAM_HANDLER, $this->_getSelfUri());
				
				if(
					$this->getPermiso()->getAuth()->hasIdentity() &&
					$this->getRequest()->getPost(Sitengine_Env::PARAM_LOGINUSER) &&
					$this->getRequest()->getPost(Sitengine_Env::PARAM_LOGINPASS)
				) {
					if($this->getRequest()->getPost(Sitengine_Env::PARAM_TARGET))
					{
						$target = $this->getRequest()->getPost(Sitengine_Env::PARAM_TARGET);
					}
					else {
						$target = $this->_getDefaultTarget();
					}
					$this->getResponse()->setRedirect($target);
					$this->getResponse()->sendResponse();
					exit;
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
				
				#$this->getLocale()->setLocale(Sitengine_Env::LANGUAGE_EN);
				
				if($this->getTranslate()->isAvailable($this->getPreferences()->getLanguage()))
				{
					#$this->getLocale()->setLocale($this->getPreferences()->getLanguage());
					$this->getTranslate()->setLocale($this->getPreferences()->getLanguage());
				}
				
				#require_once 'Zend/Registry.php';
				#Zend_Registry::set('Zend_Translate', $this->getTranslate()->getAdapter());
			}
		}
        catch (Exception $exception) {
            if($exception->getMessage() == $errorOrgNotFound) {
        		throw $this->_prepareErrorHandler($exception);
        	}
            require_once 'Sitengine/Permiso/Login/Exception.php';
            throw new Sitengine_Permiso_Login_Exception('init error', $exception);
        }
    }
    
    
    
    public function __call($method, $args)
    {
		return $this->indexAction();
    }
    
    
    
    public function indexAction()
    {
    	try {
    		$this->_start();
    		$view = $this->_getIndexViewInstance();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateIndexView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateIndexView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->setHttpResponseCode(401);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
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