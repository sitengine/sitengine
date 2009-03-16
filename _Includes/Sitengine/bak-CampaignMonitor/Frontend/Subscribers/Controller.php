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
 * @package    Sitengine_CampaignMonitor
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_CampaignMonitor_Frontend_Subscribers_Controller extends Sitengine_Controller_Action
{
    
    const ACTION_SUBSCRIBE = '_subscribe';
    const ACTION_UNSUBSCRIBE = '_unsubscribe';
    const ACTION_CONFIRM_OPTIN = '_confirmOptin';
    const ACTION_CONFIRM_FINAL = '_confirmFinal';
    const ACTION_CONFIRM_UNSUBSCRIBE = '_confirmUnsubscribe';
    const ACTION_DOSUBSCRIBE = '_doSubscribe';
    const ACTION_DOUNSUBSCRIBE = '_doUnsubscribe';
	
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
    protected $_dictionary = null;
    protected $_templateSubscribeView = null;
    protected $_templateUnsubscribeView = null;
    protected $_templateConfirmOptinView = null;
    protected $_templateConfirmFinalView = null;
    protected $_templateConfirmUnsubscribeView = null;
    
    public function getEnv() { return $this->_env; }
    public function getLogger() { return $this->_logger; }
    public function getDatabase() { return $this->_database; }
    public function getStatus() { return $this->_status; }
    public function getPreferences() { return $this->_preferences; }
    public function getLocale() { return $this->_locale; }
    public function getPermiso() { return $this->_permiso; }
    public function getNamespace() { return $this->_namespace; }
    public function getDictionary() { return $this->_dictionary; }
    public function getTranslations() { return $this->_translations; }
	
	# properties loaded from config
    protected $_ownerGroup = null;
    protected $_authorizedGroups = array();
    
    
    public function getOwnerGroup() { return $this->_ownerGroup; }
	public function getAuthorizedGroups() { return $this->_authorizedGroups; }
	
	
	
	protected $_sitemapPathSubscribeView = 'campaignMonitor/subscribers/subscribe';
	protected $_sitemapPathUnsubscribeView = 'campaignMonitor/subscribers/unsubscribe';
	protected $_sitemapPathConfirmOptinView = 'campaignMonitor/subscribers/confirmOptin';
	protected $_sitemapPathConfirmFinalView = 'campaignMonitor/subscribers/confirmFinal';
	protected $_sitemapPathConfirmUnsubscribeView = 'campaignMonitor/subscribers/confirmUnsubscribe';
	
	
	public function getSitemapPathSubscribeView() { return $this->_sitemapPathSubscribeView; }
	public function getSitemapPathUnsubscribeView() { return $this->_sitemapPathUnsubscribeView; }
	public function getSitemapPathConfirmOptinView() { return $this->_sitemapPathConfirmOptinView; }
	public function getSitemapPathConfirmFinalView() { return $this->_sitemapPathConfirmFinalView; }
	public function getSitemapPathConfirmUnsubscribeView() { return $this->_sitemapPathConfirmUnsubscribeView; }
	
	
	
	private $_viewHelper = null;
    
    public function getViewHelper()
    {
    	if($this->_viewHelper === null) {
    		$this->_viewHelper = $this->_getViewHelperInstance();
    	}
    	return $this->_viewHelper;
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
        	$this->_setSelfSubmitUri();
        	
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
			$this->_permiso = $this->getFrontController()->getPermisoPackage()->start($this->getDatabase());
        	$this->_dictionary = $this->_getDictionaryInstance();
			require_once 'Zend/Session/Namespace.php';
    		$this->_namespace = new Zend_Session_Namespace(get_class($this));
			$this->_templateSubscribeView = $this->getEnv()->getIncludesDir().'/Sitengine/CampaignMonitor/Frontend/Subscribers/_Templates/SubscribeView.html';
			$this->_templateUnsubscribeView = $this->getEnv()->getIncludesDir().'/Sitengine/CampaignMonitor/Frontend/Subscribers/_Templates/UnsubscribeView.html';
			$this->_templateConfirmOptinView = $this->getEnv()->getIncludesDir().'/Sitengine/CampaignMonitor/Frontend/Subscribers/_Templates/ConfirmOptinView.html';
			$this->_templateConfirmFinalView = $this->getEnv()->getIncludesDir().'/Sitengine/CampaignMonitor/Frontend/Subscribers/_Templates/ConfirmFinalView.html';
			$this->_templateConfirmUnsubscribeView = $this->getEnv()->getIncludesDir().'/Sitengine/CampaignMonitor/Frontend/Subscribers/_Templates/ConfirmUnsubscribeView.html';
        }
        catch (Exception $exception) {
            require_once 'Sitengine/CampaignMonitor/Frontend/Subscribers/Exception.php';
            throw new Sitengine_CampaignMonitor_Frontend_Subscribers_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_CampaignMonitor_Frontend_Front &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		$this->_frontController = $invokeArgs['frontController'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/CampaignMonitor/Frontend/Subscribers/Exception.php';
    		throw new Sitengine_CampaignMonitor_Frontend_Subscribers_Exception('invalid invoke args');
    	}
    }
    
    
    protected function _mapConfig(Zend_Config $config) {}
    
    
    
    abstract protected function _getSubscribeViewInstance();
    abstract protected function _getUnsubscribeViewInstance();
    abstract protected function _getConfirmOptinViewInstance();
    abstract protected function _getConfirmFinalViewInstance();
    abstract protected function _getConfirmUnsubscribeViewInstance();
    abstract protected function _getViewHelperInstance();
    
    
    
    protected function _getDictionaryInstance()
    {
    	require_once 'Sitengine/Dictionary.php';
        $dictionary = new Sitengine_Dictionary($this->getEnv()->getDebugControl());
        
        # english
        $dictionary->addFiles(
            Sitengine_Env::LANGUAGE_EN,
            array(
				$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/global.xml',
				$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/en.xml',
				$this->getEnv()->getIncludesDir().'/Sitengine/CampaignMonitor/Frontend/Subscribers/_Dictionary/en.xml'
            )
        );
        return $dictionary;
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
				$this->getDictionary()->readFiles($this->getPreferences()->getLanguage());
				$this->getStatus()->restore();
			}
		}
        catch (Exception $exception) {
            require_once 'Sitengine/CampaignMonitor/Frontend/Subscribers/Exception.php';
            throw new Sitengine_CampaignMonitor_Frontend_Subscribers_Exception('init error', $exception);
        }
    }
    
    
    
    protected function _setSelfSubmitUri()
    {
		$uriSelfSubmit = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		$this->getEnv()->setUriSelfSubmit($uriSelfSubmit);
    }
    
    
    
    
    protected function _goToAction($action)
    {
    	$handler = $action.'Action';
    	if(is_callable(array($this, $handler))) {
    		$this->getRequest()->setActionName($action);
    		$this->_setSelfSubmitUri();
    		call_user_func(array($this, $handler));
    	}
    	else {
    		require_once 'Sitengine/CampaignMonitor/Frontend/Subscribers/Exception.php';
    		throw new Sitengine_CampaignMonitor_Frontend_Subscribers_Exception('trying to forward to a non-existing action handler');
    	}
    }
    
    
    
    
    protected function _getResourceToActionMappings()
    {
    	return array(
    		'default' => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_SUBSCRIBE
    		),
    		Sitengine_CampaignMonitor_Frontend_Front::ROUTE_SUBSCRIBERS_SUBSCRIBE => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_SUBSCRIBE,
    			Sitengine_Env::METHOD_POST => self::ACTION_DOSUBSCRIBE
    		),
    		Sitengine_CampaignMonitor_Frontend_Front::ROUTE_SUBSCRIBERS_UNSUBSCRIBE => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_UNSUBSCRIBE,
    			Sitengine_Env::METHOD_POST => self::ACTION_DOUNSUBSCRIBE
    		),
    		Sitengine_CampaignMonitor_Frontend_Front::ROUTE_SUBSCRIBERS_CONFIRM_OPTIN => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_CONFIRM_OPTIN
    		),
    		Sitengine_CampaignMonitor_Frontend_Front::ROUTE_SUBSCRIBERS_CONFIRM_FINAL => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_CONFIRM_FINAL
    		),
    		Sitengine_CampaignMonitor_Frontend_Front::ROUTE_SUBSCRIBERS_CONFIRM_UNSUBSCRIBE => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_CONFIRM_UNSUBSCRIBE
    		)
    	);
    }
    
    
    
    
    public function factoryAction()
    {
    	$mappings = $this->_getResourceToActionMappings();
    	$route = $this->getFrontController()->getRouter()->getCurrentRouteName();
    	$method = $this->getRequest()->getMethod();
    	
    	if(!isset($mappings[$route][$method]))
    	{
    		require_once 'Sitengine/CampaignMonitor/Frontend/Subscribers/Exception.php';
    		$exception = new Sitengine_CampaignMonitor_Frontend_Subscribers_Exception(
    			'method not supported',
    			Sitengine_Env::ERROR_NOT_SUPPORTED
    		);
    		throw $this->_prepareErrorHandler($exception);
    	}
    	return $this->_goToAction($mappings[$route][$method]);
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
    
    
    
    protected function _doSubscribeAction()
    {
        try {
        	$this->_start();
        	
            if(true)
            {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORINSERT,
                	$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_ERRORINSERT),
                	true
                );
                return $this->_goToAction(self::ACTION_SUBSCRIBE);
            }
            
			$this->getStatus()->set(
				Sitengine_Env::STATUS_OKINSERT,
				$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_OKINSERT),
				false
			);
			
			$this->getStatus()->save();
			
			$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_CampaignMonitor_Frontend_Front::ROUTE_SUBSCRIBERS_CONFIRM_FINAL);
			$uri = $this->getRequest()->getBasePath().'/'.$route->assemble();
			$this->getResponse()->setRedirect($uri);
			$this->getResponse()->sendResponse();
			print ' ';
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _subscribeAction()
    {
    	try {
    		$this->_start();
    		
    		
    		$view = $this->_getSubscribeViewInstance();
			$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateSubscribeView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateSubscribeView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _unsubscribeAction()
    {
    	try {
    		$this->_start();
    		
    		
    		$view = $this->_getUnsubscribeViewInstance();
			$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateUnsubscribeView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateUnsubscribeView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _confirmOptinAction()
    {
    	try {
    		$this->_start();
    		
    		
    		$view = $this->_getConfirmOptinViewInstance();
			$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateConfirmOptinView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateConfirmOptinView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _confirmFinalAction()
    {
    	try {
    		$this->_start();
    		
    		
    		$view = $this->_getConfirmFinalViewInstance();
			$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateConfirmFinalView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateConfirmFinalView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _confirmUnsubscribeAction()
    {
    	try {
    		$this->_start();
    		
    		
    		$view = $this->_getConfirmUnsubscribeViewInstance();
			$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateConfirmUnsubscribeView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateConfirmUnsubscribeView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
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