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
 * @package    Sitengine_Newsletter
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_Newsletter_Frontend_Campaigns_Controller extends Sitengine_Controller_Action
{
    
    const ACTION_INDEX = '_index';
    const ACTION_UPDATE = '_update';
    const PARAM_FILTER_RESET = 'filterReset';
    const PARAM_FILTER_BY_TYPE = 'filterByType';
    const PARAM_FILTER_BY_FIND = 'find';
    const VALUE_NONESELECTED = 'noneSelected';
	
	
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
    protected $_entity = null;
    protected $_templateIndexView = null;
    protected $_templateDetailView = null;
    
    public function getEnv() { return $this->_env; }
    public function getLogger() { return $this->_logger; }
    public function getDatabase() { return $this->_database; }
    public function getStatus() { return $this->_status; }
    public function getPreferences() { return $this->_preferences; }
    public function getLocale() { return $this->_locale; }
    public function getPermiso() { return $this->_permiso; }
    public function getNamespace() { return $this->_namespace; }
    public function getTranslate() { return $this->_translate; }
    public function getEntity() { return $this->_entity; }
    
    
    
    
    protected $_sitemapPathCampaignsPage = 'newsletter/campaigns';
	
	
	public function getSitemapPathCampaignsPage() { return $this->_sitemapPathCampaignsPage; }
    
    
    
    
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
			
			$options = array();
			$this->getEnv()->startSession($this->getDatabase(), $options);
			$this->getFrontController()->getNewsletterPackage()->start($this->getDatabase());
			
			require_once 'Sitengine/Status.php';
			$this->_status = Sitengine_Status::getInstance();
			require_once 'Sitengine/Env/Preferences.php';
			$this->_preferences = Sitengine_Env_Preferences::getInstance();
			$this->_locale = $this->getEnv()->getLocaleInstance();
			$this->_permiso = $this->getFrontController()->getPermisoPackage()->start($this->getDatabase());
        	$this->_translate = $this->_getTranslateInstance();
        	$this->_entity = $this->_getEntityModelInstance();
			require_once 'Zend/Session/Namespace.php';
    		$this->_namespace = new Zend_Session_Namespace(get_class($this));
			$this->_templateIndexView = $this->getEnv()->getIncludesDir().'/Sitengine/Newsletter/Frontend/Campaigns/_Templates/IndexView.html';
			$this->_templateDetailView = $this->getEnv()->getIncludesDir().'/Sitengine/Newsletter/Frontend/Campaigns/_Templates/DetailView.html';
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Frontend/Campaigns/Exception.php';
            throw new Sitengine_Newsletter_Frontend_Campaigns_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_Newsletter_Frontend_Front &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		$this->_frontController = $invokeArgs['frontController'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/Newsletter/Frontend/Campaigns/Exception.php';
    		throw new Sitengine_Newsletter_Frontend_Campaigns_Exception('invalid invoke args');
    	}
    }
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    }
    
    
    
    abstract protected function _getEntityModelInstance();
    abstract protected function _getIndexViewInstance();
    abstract protected function _getDetailViewInstance();
    
    
    
    
    
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
			$this->getEnv()->getIncludesDir().'/Sitengine/Newsletter/Frontend/Campaigns/_Dictionary/en.xml'
		);
		
		$translate->addMergeTranslation($en, Sitengine_Env::LANGUAGE_EN);
		return $translate;
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
				
				# if target param is set make redirect
				if(
					$this->getPermiso()->getAuth()->hasIdentity() &&
					$this->getRequest()->getPost(Sitengine_Env::PARAM_LOGINUSER) &&
					$this->getRequest()->getPost(Sitengine_Env::PARAM_LOGINPASS) &&
					$this->getRequest()->getPost(Sitengine_Env::PARAM_TARGET)
				) {
					$target = $this->getRequest()->getPost(Sitengine_Env::PARAM_TARGET);
					$this->getResponse()->setRedirect($target);
					$this->getResponse()->sendResponse();
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
				
				#$this->getLocale()->setLocale(Sitengine_Env::LANGUAGE_EN);
				
				if($this->getTranslate()->isAvailable($this->getPreferences()->getLanguage()))
				{
					#$this->getLocale()->setLocale($this->getPreferences()->getLanguage());
					$this->getTranslate()->setLocale($this->getPreferences()->getLanguage());
				}
				
				#require_once 'Zend/Registry.php';
				#Zend_Registry::set('Zend_Translate', $this->getTranslate()->getAdapter());
				
				
				$this->getStatus()->restore();
				if($this->getStatus()->getCode() != Sitengine_Env::STATUS_OKINSERT) {
					$this->getStatus()->reset();
				}
			}
		}
        catch (Exception $exception) {
            require_once 'Sitengine/Newsletter/Frontend/Campaigns/Exception.php';
            throw new Sitengine_Newsletter_Frontend_Campaigns_Exception('init error', $exception);
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
    		#$this->_setSelfSubmitUri();
    		call_user_func(array($this, $handler));
    	}
    	else {
    		require_once 'Sitengine/Newsletter/Frontend/Campaigns/Exception.php';
    		throw new Sitengine_Newsletter_Frontend_Campaigns_Exception('trying to forward to a non-existing action handler');
    	}
    }
    
    
    
    protected function _getResourceToActionMappings()
    {
    	return array(
    		'default' => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_INDEX
    		),
    		Sitengine_Newsletter_Frontend_Front::ROUTE_CAMPAIGNS => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_INDEX
    		),
    		Sitengine_Newsletter_Frontend_Front::ROUTE_CAMPAIGNS_SHARP => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_UPDATE
    		),
    	);
    }
    
    
    
    
    public function factoryAction()
    {
    	$mappings = $this->_getResourceToActionMappings();
    	$route = $this->getFrontController()->getRouter()->getCurrentRouteName();
    	$method = $this->getRequest()->getMethod();
    	
    	if(!isset($mappings[$route][$method]))
    	{
    		require_once 'Sitengine/Newsletter/Frontend/Campaigns/Exception.php';
    		$exception = new Sitengine_Newsletter_Frontend_Campaigns_Exception(
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
    
    
    
    
    
    protected function _indexAction()
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
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    
    protected function _updateAction()
    {
    	try {
    		$this->_start();
    		$this->getEntity()->start();
			$view = $this->_getDetailViewInstance();
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateDetailView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateDetailView));
    		$body .= $this->_getDebugDump($view->getData());
    		$body = preg_replace('/%UNSUBSCRIBE%(.*)%UNSUBSCRIBE%/', "<unsubscribe>$1</unsubscribe>", $body);
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