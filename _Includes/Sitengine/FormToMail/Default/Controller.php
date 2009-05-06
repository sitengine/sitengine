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


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_FormToMail_Default_Controller extends Sitengine_Controller_Action
{
	
    const ACTION_INDEX = 'index';
    const ACTION_CONFIRM = 'confirm';
    const ACTION_SENDMAIL = 'sendMail';

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
    protected $_indexViewTemplate = null;
    protected $_confirmViewTemplate = null;
    protected $_messageTemplate = null;
    
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
    
    
    
    public function getMessageTemplate()
    {
    	return $this->_messageTemplate;
    }
    
    
    
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
        	#$this->_setSelfSubmitUri();
        	
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
			$this->_indexViewTemplate = $this->getEnv()->getIncludesDir().'/Sitengine/FormToMail/Default/_Templates/IndexView.html';
			$this->_confirmViewTemplate = $this->getEnv()->getIncludesDir().'/Sitengine/FormToMail/Default/_Templates/ConfirmView.html';
			$this->_messageTemplate = $this->getEnv()->getIncludesDir().'/Sitengine/FormToMail/Default/_Templates/Message.html';
        }
        catch (Exception $exception) {
            require_once 'Sitengine/FormToMail/Default/Exception.php';
            throw new Sitengine_FormToMail_Default_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_FormToMail_Front &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		$this->_frontController = $invokeArgs['frontController'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/FormToMail/Default/Exception.php';
    		throw new Sitengine_FormToMail_Default_Exception('invalid invoke args');
    	}
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	$config = $config->indexController;
    	/*
    	if(isset($config->xxx))
		{
			$this->_xxx = $config->xxx;
		}
		else {
			require_once 'Sitengine/FormToMail/Default/Exception.php';
			throw new Sitengine_FormToMail_Default_Exception('action controller config error');
		}
		*/
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
            require_once 'Sitengine/FormToMail/Default/Exception.php';
            throw new Sitengine_FormToMail_Default_Exception('init error', $exception);
        }
    }
    
    
    protected function _getTranslateInstance()
    {
		require_once 'Sitengine/Translate.php';
		$translate = new Sitengine_Translate(
			Sitengine_Translate::AN_XML,
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/global.xml',
			Sitengine_Env::LANGUAGE_EN
		);
		
		$en = array(
			$this->getEnv()->getIncludesDir().'/Sitengine/FormToMail/Default/_Translations/en.xml'
		);
		
		$de = array(
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/global.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/FormToMail/Default/_Translations/de.xml'
		);
		
		$translate->addMergeTranslation($en, Sitengine_Env::LANGUAGE_EN);
		$translate->addMergeTranslation($de, Sitengine_Env::LANGUAGE_DE);
		return $translate;
    }
    
    
    
    abstract protected function _getMailerInstance();
    abstract protected function _getIndexViewInstance();
    abstract protected function _getConfirmViewInstance();
    abstract public function getMessageInstance();
    
    
    
    protected function _setSelfSubmitUri()
    {
		$uriSelfSubmit = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		$this->getEnv()->setUriSelfSubmit($uriSelfSubmit);
    }
    
    
    
    protected function _forwardToLogin()
    {
    	$target = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		$this->getRequest()->setParam(Sitengine_Env::PARAM_TARGET, $target);
    	
    	$this->_forward(
    		self::ACTION_INDEX,
    		$this->getFrontController()->getController(Sitengine_FormToMail_Front::CONTROLLER_LOGIN)
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
    		require_once 'Sitengine/FormToMail/Default/Exception.php';
    		throw new Sitengine_FormToMail_Default_Exception('trying to forward to a non-existing action handler');
    	}
    }
    
    
    
    
    public function restMapperAction()
    {
    	$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
    	$method = $this->getRequest()->getIntendedMethod();
    	$action = null;
    	#print $routeName;
    	#print $method;
    	#exit;
    	
    	switch($routeName)
    	{
    		case 'default':
    		{
    			$action = self::ACTION_INDEX;
    			break;
    		}
    		case Sitengine_FormToMail_Front::ROUTE_INDEX:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INDEX; break;
    				case Sitengine_Env::METHOD_POST: $action = self::ACTION_SENDMAIL; break;
    			}
    			break;
    		}
    		case Sitengine_FormToMail_Front::ROUTE_CONFIRM:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_CONFIRM; break;
    			}
    			break;
    		}
    	}
    	if($action === null)
    	{
    		require_once 'Sitengine/FormToMail/Default/Exception.php';
    		$exception = new Sitengine_FormToMail_Default_Exception(
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
    
    
    
    public function indexAction()
    {
    	try {
    		$this->_start();
			if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getFormToMailPackage()->getAuthorizedGroups())) {
				return $this->_forwardToLogin();
			}
			
			$group = $this->getTranslate()->translateGroup('group')->toArray();
			Sitengine_Debug::print_r($group);
			
			$view = $this->_getIndexViewInstance();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_indexViewTemplate));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_indexViewTemplate));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    public function confirmAction()
    {
    	try {
    		$this->_start();
			if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getFormToMailPackage()->getAuthorizedGroups())) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getConfirmViewInstance();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_confirmViewTemplate));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_confirmViewTemplate));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    const STATUS_SEND_OK = 'statusSendOk';
    const STATUS_SEND_ERROR = 'statusSendError';
    
    
    public function sendMailAction()
    {
    	try {
			$this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getFormToMailPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
            	require_once 'Sitengine/FormToMail/Default/Exception.php';
            	throw new Sitengine_FormToMail_Default_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            
            $mailer = $this->_getMailerInstance();
			
			if(!$mailer->checkInput())
			{
				$this->getStatus()->set(
					self::STATUS_SEND_ERROR,
					$this->getTranslate()->translate(self::STATUS_SEND_ERROR),
					true
				);
				return $this->_goToAction(self::ACTION_INDEX);
			}
			
			$this->getStatus()->set(
				self::STATUS_SEND_OK,
				$this->getTranslate()->translate(self::STATUS_SEND_OK),
				true
			);
			
			$mailer->send();
			$this->getStatus()->save();
			
			$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_FormToMail_Front::ROUTE_CONFIRM);
			$uri = $this->getRequest()->getBasePath().'/'.$route->assemble(array());
			$this->getResponse()->setRedirect($uri);
			$this->getResponse()->sendResponse();
			print ' ';
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