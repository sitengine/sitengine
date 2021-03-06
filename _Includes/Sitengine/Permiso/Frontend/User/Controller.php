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


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_Permiso_Frontend_User_Controller extends Sitengine_Controller_Action
{
    
    const ACTION_INDEX = '_index';
    const ACTION_INSERT = '_insert';
    const ACTION_DOINSERT = '_doInsert';
    const ACTION_UPDATE = '_update';
    const ACTION_DOUPDATE = '_doUpdate';
    
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
    protected $_templateJoinView = null;
    protected $_templateFormView = null;
    
    
    
    private $_viewHelper = null;
    
    public function getViewHelper()
    {
    	if($this->_viewHelper === null) {
    		$this->_viewHelper = $this->_getViewHelperInstance();
    	}
    	return $this->_viewHelper;
    }
    
    abstract protected function _getViewHelperInstance();
    
    
    
    
    private $_modifier = null;
    
    public function getModifier()
    {
    	if($this->_modifier === null) {
    		$this->_modifier = $this->_getModifierInstance();
    	}
    	return $this->_modifier;
    }
    
    abstract protected function _getModifierInstance();
    
    
    
    
    
    private $_sections = null;
    
    public function getSections()
    {
    	if($this->_sections === null) {
    		$this->_sections = $this->_getSectionsInstance();
    	}
    	return $this->_sections;
    }
    
    abstract protected function _getSectionsInstance();
    
    
    
    
    
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
    	Zend_Controller_Request_Abstract $request,
    	Zend_Controller_Response_Abstract $response,
    	array $invokeArgs = array()
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
            require_once 'Sitengine/Permiso/Frontend/User/Exception.php';
            throw new Sitengine_Permiso_Frontend_User_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    
    protected function _setTemplates()
    {
        $this->_templateJoinView = $this->getEnv()->getIncludesDir().'/Sitengine/Permiso/Frontend/User/_Templates/JoinView.html';
        $this->_templateFormView = $this->getEnv()->getIncludesDir().'/Sitengine/Permiso/Frontend/User/_Templates/FormView.html';
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		#array_key_exists('package', $invokeArgs) &&
    		#$invokeArgs['package'] instanceof Sitengine_Permiso_Package &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_Permiso_Frontend_Front &&
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
    		require_once 'Sitengine/Permiso/Frontend/User/Exception.php';
    		throw new Sitengine_Permiso_Frontend_User_Exception('invalid invoke args');
    	}
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {}
    
    
    
    abstract protected function _getFormViewInstance();
    
    
	
	
    protected function _getTranslateInstance()
    {
    	require_once 'Sitengine/Translate.php';
		$translate = new Sitengine_Translate(
			Sitengine_Translate::AN_XML,
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/global.xml',
			Sitengine_Env::LANGUAGE_EN
		);
		
		$en = array(
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/en.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/Permiso/_Dictionary/en.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/Permiso/Frontend/User/_Dictionary/en.xml'
		);
		
		$translate->addMergeTranslation($en, Sitengine_Env::LANGUAGE_EN);
		return $translate;
    }
    
    
    
    protected function _start()
    {
    	try {
    		$errorOrgNotFound = 'organization not found';
    		
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
				
				#$this->getLocale()->setLocale(Sitengine_Env::LANGUAGE_EN);
				
				if($this->getTranslate()->isAvailable($this->getPreferences()->getLanguage()))
				{
					#$this->getLocale()->setLocale($this->getPreferences()->getLanguage());
					$this->getTranslate()->setLocale($this->getPreferences()->getLanguage());
				}
				
				#require_once 'Zend/Registry.php';
				#Zend_Registry::set('Zend_Translate', $this->getTranslate()->getAdapter());
				
				
				$this->getStatus()->restore();
			}
		}
        catch (Exception $exception) {
            if($exception->getMessage() == $errorOrgNotFound) {
        		throw $this->_prepareErrorHandler($exception);
        	}
            require_once 'Sitengine/Permiso/Frontend/User/Exception.php';
            throw new Sitengine_Permiso_Frontend_User_Exception('init error', $exception);
        }
    }
    
    
    
    protected function _setSelfSubmitUri()
    {
		$uriSelfSubmit = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		#$this->getEnv()->setUriSelfSubmit($uriSelfSubmit);
    }
    
    
    
    protected function _forwardToLogin()
    {
    	$target = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		$this->getRequest()->setParam(Sitengine_Env::PARAM_TARGET, $target);
    	
    	$this->_forward(
    		self::ACTION_INDEX,
    		$this->getFrontController()->getController(Sitengine_Permiso_Frontend_Front::CONTROLLER_LOGIN)
    	);
    }
    
    
    
    
    protected function _goToAction($action)
    {
    	$handler = $action.'Action';
    	if(is_callable(array($this, $handler))) {
    		$this->getRequest()->setActionName($action);
    		#$this->_setSelfSubmitUri();
    		call_user_func(array($this, $handler));
    	}
    	else {
    		require_once 'Sitengine/Permiso/Frontend/User/Exception.php';
    		throw new Sitengine_Permiso_Frontend_User_Exception('trying to forward to a non-existing action handler');
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
    
    
    
    public function restMapperAction()
    {
    	$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
    	$method = $this->getRequest()->getIntendedMethod();
    	$action = null;
    	
    	switch($routeName)
    	{
    		case Sitengine_Permiso_Frontend_Front::ROUTE_USER:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_UPDATE; break;
    				case Sitengine_Env::METHOD_POST: $action = self::ACTION_DOUPDATE; break;
    			}
    			break;
    		}
    		case Sitengine_Permiso_Frontend_Front::ROUTE_USER_NEW:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INSERT; break;
    				case Sitengine_Env::METHOD_POST: $action = self::ACTION_DOINSERT; break;
    			}
    			break;
    		}
    		case 'default':
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_UPDATE; break;
    			}
    			break;
    		}
    	}
    	if($action === null) {
    		require_once 'Sitengine/Permiso/Frontend/User/Exception.php';
    		$exception = new Sitengine_Permiso_Frontend_User_Exception(
    			"'$method' not supported on route '$route'",
    			Sitengine_Env::ERROR_NOT_FOUND
    		);
    		throw $this->_prepareErrorHandler($exception);
    	}
    	return $this->_goToAction($action);
    }
    
    
    
    
    protected function _insertAction()
    {
		try {
    		$this->_start();
    		$this->getPermiso()->getAuth()->clearIdentity();
    		
    		$view = $this->_getFormViewInstance();
    		
    		$view->controller = $this;
    		
    		$view->env = $this->getEnv();
    		
    		$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateJoinView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateJoinView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _doInsertAction()
    {
        try {
            $this->_start();
            $this->getPermiso()->getAuth()->clearIdentity();
        	
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Permiso/Frontend/User/Exception.php';
                throw new Sitengine_Permiso_Frontend_User_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            
            $data = $this->getModifier()->insert();
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORINSERT),
                	true
                );
                return $this->_goToAction(self::ACTION_INSERT);
            }
            else {
            	$this->getPermiso()->getAuth()->reauthenticate(
            		$this->getPermiso()->getAuthAdapter(),
            		$data['id']
            	);
            	
				$message = $this->getTranslate()->translate(Sitengine_Env::STATUS_OKINSERT);
    			$this->getStatus()->set(Sitengine_Env::STATUS_OKINSERT, $message, false);
    			$this->getStatus()->save();
    			
    			$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Permiso_Frontend_Front::ROUTE_USER);
				$uri = $this->getRequest()->getBasePath().'/'.$route->assemble();
                $this->getResponse()->setRedirect($uri);
                $this->getResponse()->sendResponse();
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _updateAction()
    {
		try {
    		$this->_start();
    		
    		if(!$this->getPermiso()->getAcl()->authenticatedAccessGranted()) {
				return $this->_forwardToLogin();
			}
    		$view = $this->_getFormViewInstance();
    		$view->controller = $this;
    		$view->env = $this->getEnv();
    		$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function _doUpdateAction()
    {
        try {
        	$this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->authenticatedAccessGranted()) {
    			return $this->_forwardToLogin();
			}
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Permiso/Frontend/User/Exception.php';
                throw new Sitengine_Permiso_Frontend_User_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            
            $data = $this->getModifier()->update();
            
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORUPDATE),
                	true
                );
                return $this->_goToAction(self::ACTION_UPDATE);
            }
            else {
            	$this->getPermiso()->getAuth()->reauthenticate(
            		$this->getPermiso()->getAuthAdapter(),
            		$this->getPermiso()->getAuth()->getId()
            	);
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKUPDATE),
                	false
                );
                return $this->_goToAction(self::ACTION_UPDATE);
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
}


?>