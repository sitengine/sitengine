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


abstract class Sitengine_Blog_Backend_Blogs_Controller extends Sitengine_Controller_Action
{
    
    const ACTION_INDEX = '_index';
    const ACTION_UPDATE = '_update';
    const ACTION_INSERT = '_insert';
    const ACTION_DOUPDATE = '_doUpdate';
    const ACTION_DOINSERT = '_doInsert';
    const ACTION_DOBATCHDELETE = '_doBatchDelete';
    const ACTION_DOBATCHUPDATE = '_doBatchUpdate';
    const PARAM_FILTER_RESET = 'resetFilter';
    const PARAM_FILTER_BY_FIND = 'find';
    #const PARAM_SETTINGS_RESET = 'resetSettings';
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
    protected $_markedRows = array();
    protected $_templateIndexView = null;
    protected $_templateFormView = null;
    
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
    public function getMarkedRows() { return $this->_markedRows; }
    
    
    
    
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
			
			$this->getEnv()->startSession($this->getDatabase());
			$this->getFrontController()->getBlogPackage()->start($this->getDatabase());
			
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
			$this->_templateIndexView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Backend/Blogs/_Templates/IndexView.html';
			$this->_templateFormView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Backend/Blogs/_Templates/FormView.html';
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_Blog_Backend_Front &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		$this->_frontController = $invokeArgs['frontController'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Exception('invalid invoke args');
    	}
    }
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	/*
    	$config = $config->{$this->getFrontController()->getConfigName()}->backend->blogsController;
    	
    	if(
			isset($config->ownerGroup) &&
			isset($config->authorizedGroups)
		)
		{
			$this->_ownerGroup = $config->ownerGroup;
			$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
			throw new Sitengine_Blog_Backend_Blogs_Exception('action controller config error');
		}
		*/
    }
    
    
    
    abstract protected function _getEntityModelInstance();
    abstract protected function _getModifierModelInstance();
    abstract protected function _getIndexViewInstance();
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
			$this->getEnv()->getIncludesDir().'/Sitengine/Blog/Backend/_Dictionary/en.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/Blog/Backend/Blogs/_Dictionary/en.xml'
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
				if(
					$this->getStatus()->getCode() != Sitengine_Env::STATUS_OKINSERT &&
					$this->getStatus()->getCode() != Sitengine_Env::STATUS_OKUPDATE
				) {
					$this->getStatus()->reset();
				}
			}
		}
        catch (Exception $exception) {
        	if($exception->getMessage() == $errorOrgNotFound) {
        		throw $this->_prepareErrorHandler($exception);
        	}
            require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            throw new Sitengine_Blog_Backend_Blogs_Exception('init error', $exception);
        }
    }
    
    
    
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
    		'index',
    		$this->getFrontController()->getController(Sitengine_Blog_Backend_Front::CONTROLLER_LOGIN)
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
    		require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
    		throw new Sitengine_Blog_Backend_Blogs_Exception('trying to forward to a non-existing action handler');
    	}
    }
    
    
    
    public function factoryAction()
    {
    	$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
    	$method = $this->getRequest()->getMethod();
    	$action = null;
    	
    	switch($routeName)
    	{
    		case 'default':
    		{
    			$action = self::ACTION_INDEX;
    			break;
    		}
    		case Sitengine_Blog_Backend_Front::ROUTE_BLOGS:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INDEX; break;
    			}
    			break;
    		}
    		case Sitengine_Blog_Backend_Front::ROUTE_BLOGS_NEW:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INSERT; break;
    				case Sitengine_Env::METHOD_POST: $action = self::ACTION_DOINSERT; break;
    			}
    			break;
    		}
    		case Sitengine_Blog_Backend_Front::ROUTE_BLOGS_BATCH:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INDEX; break;
    				case Sitengine_Env::METHOD_PUT: $action = self::ACTION_DOBATCHUPDATE; break;
    				case Sitengine_Env::METHOD_DELETE: $action = self::ACTION_DOBATCHDELETE; break;
    			}
    			break;
    		}
    		case Sitengine_Blog_Backend_Front::ROUTE_BLOGS_SHARP:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_UPDATE; break;
    				case Sitengine_Env::METHOD_PUT: $action = self::ACTION_DOUPDATE; break;
    			}
    			break;
    		}
    	}
    	if($action === null) {
    		require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
    		$exception = new Sitengine_Blog_Backend_Blogs_Exception(
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
    
    
    
    
    protected function _doUpdateAction()
    {
        try {
        	$this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
                throw new Sitengine_Blog_Backend_Blogs_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            /*
            $tables = array(
                $this->getFrontController()->getBlogPackage()->getBlogsTableName() => 'WRITE'
            );
            # lock tables
            require_once 'Sitengine/Sql.php';
			$q = Sitengine_Sql::getLockQuery($tables);
			$this->getDatabase()->getConnection()->exec($q);
            */
            if(!$this->getEntity()->start()) {
                return $this->_forwardToLogin();
            }
            
            $modifier = $this->_getModifierModelInstance();
            $data = $modifier->update();
            #$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORUPDATE),
                	true
                );
                return $this->_goToAction(self::ACTION_UPDATE);
            }
            else {
                $this->getEntity()->refresh($data);
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKUPDATE),
                	false
                );
                $this->getStatus()->save();
                
                $query = array(
                    Sitengine_Env::PARAM_SORT => 'mdate',
                    Sitengine_Env::PARAM_ORDER => 'desc'
                );
                $args = array(
                	Sitengine_Env::PARAM_ID => $this->getEntity()->getSlug()
                );
                $route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS_SHARP);
                $uri  = $this->getRequest()->getBasePath().'/'.$route->assemble($args);
                $uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query, '&');
                $this->getResponse()->setRedirect($uri);
                $this->getResponse()->sendResponse();
                
                #return $this->_goToAction(self::ACTION_UPDATE);
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _doInsertAction()
    {
        try {
        	$this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
            	require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            	throw new Sitengine_Blog_Backend_Blogs_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            $data = $modifier->insert();
            
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORINSERT),
                	true
                );
                return $this->_goToAction(self::ACTION_INSERT);
            }
            else {
            	$this->getStatus()->set(
                	Sitengine_Env::STATUS_OKINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKINSERT),
                	false
                );
                $this->getStatus()->save();
                
                # avoid double submits
                $query = array(
                    Sitengine_Env::PARAM_SORT => 'cdate',
                    Sitengine_Env::PARAM_ORDER => 'desc'
                );
                $route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Backend_Front::ROUTE_BLOGS);
                $uri  = $this->getRequest()->getBasePath().'/'.$route->assemble();
                $uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query, '&');
                $this->getResponse()->setRedirect($uri);
                $this->getResponse()->sendResponse();
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _doBatchDeleteAction()
    {
        try {
        	$this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
            	require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            	throw new Sitengine_Blog_Backend_Blogs_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            $deleted = 0;
            $rows = Sitengine_Controller_Request_Http::getSelectedRows($_POST);
            /*
            $tables = array(
                $this->getFrontController()->getBlogPackage()->getBlogsTableName() => 'WRITE',
                $this->getFrontController()->getBlogPackage()->getPostsTableName() => 'WRITE',
                $this->getFrontController()->getBlogPackage()->getCommentsTableName() => 'WRITE',
                $this->getFrontController()->getBlogPackage()->getFilesTableName() => 'WRITE'
            );
            */
            if(sizeof($rows) > 0) {
            	/*
                # lock tables
                require_once 'Sitengine/Sql.php';
				$q = Sitengine_Sql::getLockQuery($tables);
				$this->getDatabase()->getConnection()->exec($q);
                */
                foreach($rows as $id => $v) {
                    $affectedRows = $modifier->delete($id);
                    if($affectedRows > 0) { $deleted++; }
                    else { $this->_markedRows[$id] = 1; }
                }
                if($deleted < sizeof($rows)) {
                	$this->getStatus()->set(
                		Sitengine_Env::STATUS_ERRORBATCHTRASH,
                		$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORBATCHTRASH),
                		true
                	);
                }
                else if(sizeof($rows) > 0) {
                	$this->getStatus()->set(
                		Sitengine_Env::STATUS_OKBATCHTRASH,
                		$this->getTranslate()->translate(Sitengine_Env::STATUS_OKBATCHTRASH),
                		false
                	);
                }
                #$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            }
            return $this->_goToAction(self::ACTION_INDEX);
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _doBatchUpdateAction()
    {
        try {
        	$this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
            	require_once 'Sitengine/Blog/Backend/Blogs/Exception.php';
            	throw new Sitengine_Blog_Backend_Blogs_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            $updated = 0;
            $rows = Sitengine_Controller_Request_Http::getModifiedRows($_POST);
            /*
            $tables = array(
                $this->getFrontController()->getBlogPackage()->getBlogsTableName() => 'WRITE'
            );
            */
            if(sizeof($rows) > 0) {
            	/*
                # lock tables
                require_once 'Sitengine/Sql.php';
				$q = Sitengine_Sql::getLockQuery($tables);
				$this->getDatabase()->getConnection()->exec($q);
                */
                foreach($rows as $id => $data)
                {
                    $affectedRows = $modifier->updateFromList($id, $data, $this->getFrontController()->getBlogPackage()->getAuthorizedGroups());
                    if($affectedRows > 0) { $updated++; }
                    else { $this->_markedRows[$id] = 1; }
                }
               	#$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
                
                if($updated < sizeof($rows)) {
                	$this->getStatus()->set(
                		Sitengine_Env::STATUS_ERRORBATCHUPDATE,
                		$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORBATCHUPDATE),
                		true
                	);
                }
                else if(sizeof($rows) > 0) {
                	$this->getStatus()->set(
                		Sitengine_Env::STATUS_OKBATCHUPDATE,
                		$this->getTranslate()->translate(Sitengine_Env::STATUS_OKBATCHUPDATE),
                		false
                	);
                }
            }
            return $this->_goToAction(self::ACTION_INDEX);
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _indexAction()
    {
    	try {
    		$this->_start();
			if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
				return $this->_forwardToLogin();
			}
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
    
    
    
    
    protected function _insertAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            $view = $this->_getFormViewInstance();
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
    
    
    
    
    protected function _updateAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
			if(!$this->getEntity()->start()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getFormViewInstance();
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
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