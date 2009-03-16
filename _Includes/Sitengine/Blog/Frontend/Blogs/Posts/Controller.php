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
 * @package    Sitengine_Blog
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Controller extends Sitengine_Controller_Action
{
    
    const ACTION_INDEX = '_index';
    const ACTION_VIEW = '_view';
    #const ACTION_INSERT = '_insert';
    #const ACTION_DOUPDATE = '_doUpdate';
    #const ACTION_DOINSERT = '_doInsert';
    #const ACTION_DOBATCHDELETE = '_doBatchDelete';
    #const ACTION_DOBATCHUPDATE = '_doBatchUpdate';
	const PARAM_FILTER_RESET = 'resetFilter';
    const PARAM_FILTER_BY_TYPE = 'filterByType';
    const PARAM_FILTER_BY_UID = 'filterByUid';
    const PARAM_FILTER_BY_GID = 'filterByGid';
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
    protected $_dictionary = null;
    protected $_entity = null;
    protected $_markedRows = array();
    protected $_templateIndexView = null;
    protected $_templateTextView = null;
    protected $_templatePhotoView = null;
    protected $_templateGalleryView = null;
    protected $_templateQuoteView = null;
    protected $_templateLinkView = null;
    protected $_templateAudioView = null;
    protected $_templateVideoView = null;
    
    
    public function getEnv() { return $this->_env; }
    public function getLogger() { return $this->_logger; }
    public function getDatabase() { return $this->_database; }
    public function getStatus() { return $this->_status; }
    public function getPreferences() { return $this->_preferences; }
    public function getLocale() { return $this->_locale; }
    public function getPermiso() { return $this->_permiso; }
    public function getNamespace() { return $this->_namespace; }
    public function getDictionary() { return $this->_dictionary; }
    public function getEntity() { return $this->_entity; }
    #public function getMarkedRows() { return $this->_markedRows; }
    
	
    
    
    
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
			$this->getFrontController()->getBlogPackage()->start($this->getDatabase());
			
			require_once 'Sitengine/Status.php';
			$this->_status = Sitengine_Status::getInstance();
			require_once 'Sitengine/Env/Preferences.php';
			$this->_preferences = Sitengine_Env_Preferences::getInstance();
			$this->_locale = $this->getEnv()->getLocaleInstance();
			$this->_permiso = $this->getFrontController()->getPermisoPackage()->start($this->getDatabase());
        	$this->_dictionary = $this->_getDictionaryInstance();
        	$this->_entity = $this->_getEntityModelInstance();
			require_once 'Zend/Session/Namespace.php';
    		$this->_namespace = new Zend_Session_Namespace(get_class($this));
			$this->_templateIndexView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/_Templates/IndexView.html';
			$this->_templateTextView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/_Templates/TextView.html';
			$this->_templatePhotoView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/_Templates/PhotoView.html';
			$this->_templateGalleryView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/_Templates/GalleryView.html';
			$this->_templateQuoteView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/_Templates/QuoteView.html';
			$this->_templateLinkView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/_Templates/LinkView.html';
			$this->_templateAudioView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/_Templates/AudioView.html';
			$this->_templateVideoView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/_Templates/VideoView.html';
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_Blog_Frontend_Front &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		$this->_frontController = $invokeArgs['frontController'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('invalid invoke args');
    	}
    }
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	/*
    	$config = $config->{$this->getFrontController()->getConfigName()}->frontend->blogsPostsController;
    	
    	if(
			#isset($config->ownerGroup) &&
			isset($config->authorizedGroups)
		)
		{
			#$this->_ownerGroup = $config->ownerGroup;
			$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('action controller config error');
		}
		*/
    }
    
    
    
    abstract protected function _getEntityModelInstance();
    #abstract protected function _getModifierModelInstance();
    abstract protected function _getIndexViewInstance();
    abstract protected function _getTextViewInstance();
    abstract protected function _getPhotoViewInstance();
    abstract protected function _getGalleryViewInstance();
    abstract protected function _getQuoteViewInstance();
    abstract protected function _getLinkViewInstance();
    abstract protected function _getAudioViewInstance();
    abstract protected function _getVideoViewInstance();
	
	
	
	
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
				$this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/_Dictionary/en.xml',
				$this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/_Dictionary/en.xml'
			)
        );
        return $dictionary;
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
            if($exception->getMessage() == $errorOrgNotFound) {
        		throw $this->_prepareErrorHandler($exception);
        	}
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('init error', $exception);
        }
    }
    
    
    
    protected function _setSelfSubmitUri()
    {
    	$uriSelfSubmit = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		$this->getEnv()->setUriSelfSubmit($uriSelfSubmit);
    }
    
    
    /*
    protected function _forwardToLogin()
    {
    	$target = preg_replace('/\?.* /', '', $_SERVER['REQUEST_URI']);
		$this->getRequest()->setParam(Sitengine_Env::PARAM_TARGET, $target);
    	
    	$this->_forward(
    		'index',
    		$this->getFrontController()->getController(Sitengine_Blog_Frontend_Front::CONTROLLER_LOGIN)
    	);
    }
    */
    
    
    protected function _goToAction($action)
    {
    	$handler = $action.'Action';
    	if(is_callable(array($this, $handler))) {
    		$this->getRequest()->setActionName($action);
    		$this->_setSelfSubmitUri();
    		call_user_func(array($this, $handler));
    	}
    	else {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Exception('trying to forward to a non-existing action handler');
    	}
    }
    
    
    
    public function factoryAction()
    {
    	$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
    	$method = $this->getRequest()->getMethod();
    	$action = null;
    	
    	switch($routeName)
    	{
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS:
    		{
    			switch($method) {
    				# this route is used to go directly to a specific blog
    				# by overwriting the ROUTE_BLOGS and pass the slug of a specific blog
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INDEX; break;
    			}
    			break;
    		}
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INDEX; break;
    			}
    			break;
    		}
    		/*
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_NEW:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INSERT; break;
    				case Sitengine_Env::METHOD_POST: $action = self::ACTION_DOINSERT; break;
    			}
    			break;
    		}
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_BATCH:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INDEX; break;
    				case Sitengine_Env::METHOD_PUT: $action = self::ACTION_DOBATCHUPDATE; break;
    				case Sitengine_Env::METHOD_DELETE: $action = self::ACTION_DOBATCHDELETE; break;
    			}
    			break;
    		}
    		*/
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_SHARP:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_VIEW; break;
    				#case Sitengine_Env::METHOD_PUT: $action = self::ACTION_DOUPDATE; break;
    			}
    			break;
    		}
    		default:
    		{
    			switch($method) {
    				# this route is used to go directly to a specific blog
    				# by overwriting the ROUTE_BLOGS and pass the slug of a specific blog
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INDEX; break;
    			}
    			break;
    		}
    	}
    	if($action === null) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Exception.php';
    		$message = 'method not supported - '.$_SERVER['REQUEST_URI'].' ('.$method.')';
    		$exception = new Sitengine_Blog_Frontend_Blogs_Posts_Exception(
    			$message,
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
    
    
    
    protected function _indexAction()
    {
    	try {
    		$representation = $this->getRequest()->get(Sitengine_Env::PARAM_REPRESENTATION);
    		
    		if($representation == 'xml')
    		{
    			$this->_start();
				$this->getEntity()->start();
				print $this->_getIndexViewInstance()->getAtom();
				exit;
    		}
    		else if($representation == 'test')
    		{
    			$this->_start();
				$this->getEntity()->start();
				print $this->_getIndexViewInstance()->getTestFeed();
				exit;
    		}
    		else {
				$this->_start();
				if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
					#return $this->_forwardToLogin();
				}
				if(!$this->getEntity()->start()) {
					#return $this->_forwardToLogin();
				}
				$view = $this->_getIndexViewInstance();
				$view->setHelperPath($this->getEnv()->getIncludesDir());
				$view->setScriptPath(dirname($this->_templateIndexView));
				$view->doctype()->setDoctype('XHTML1_STRICT');
				$view->build()->batchAssign($view->getData());
				$body  = $view->render(basename($this->_templateIndexView));
				$body .= $this->_getDebugDump($view->getData());
				$this->getResponse()->setBody($body);
				$this->getResponse()->sendResponse();
			}
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _viewAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                #return $this->_forwardToLogin();
            }
            if(!$this->getEntity()->start()) {
				return $this->_forwardToLogin();
			}
			$stored = $this->getEntity()->getRow()->toArray();
			
			if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_PHOTO) {
				$view = $this->_getPhotoViewInstance();
				$template = $this->_templatePhotoView;
			}
			else if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_GALLERY) {
				$view = $this->_getGalleryViewInstance();
				$template = $this->_templateGalleryView;
			}
			else if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_QUOTE) {
				$view = $this->_getQuoteViewInstance();
				$template = $this->_templateQuoteView;
			}
			else if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_LINK) {
				$view = $this->_getLinkViewInstance();
				$template = $this->_templateLinkView;
			}
			else if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_AUDIO) {
				$view = $this->_getAudioViewInstance();
				$template = $this->_templateAudioView;
			}
			else if($stored['type'] == Sitengine_Blog_Posts_Table::TYPE_VIDEO) {
				$view = $this->_getVideoViewInstance();
				$template = $this->_templateVideoView;
			}
			else {
				$view = $this->_getTextViewInstance();
				$template = $this->_templateTextView;
			}
			#Sitengine_Debug::print_r($stored);
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
			$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($template));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($template));
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