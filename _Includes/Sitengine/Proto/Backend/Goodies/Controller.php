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


abstract class Sitengine_Proto_Backend_Goodies_Controller extends Sitengine_Controller_Action
{
    
    const ACTION_INDEX = '_index';
    const ACTION_UPDATE = '_update';
    const ACTION_INSERT = '_insert';
    const ACTION_UPLOAD = '_upload';
    const ACTION_ASSIGN = '_assign';
    const ACTION_DOUPDATE = '_doUpdate';
    const ACTION_DOINSERT = '_doInsert';
    const ACTION_DOUPLOAD = '_doUpload';
    const ACTION_DOBATCHDELETE = '_doBatchDelete';
    const ACTION_DOBATCHUPDATE = '_doBatchUpdate';
    const ACTION_DOBATCHASSIGN = '_doBatchAssign';
    const ACTION_DOBATCHUNLINK = '_doBatchUnlink';
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
    protected $_templateFormView = null;
    protected $_templateUploadView = null;
    protected $_templateAssignView = null;
    
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
    public function getMarkedRows() { return $this->_markedRows; }
    
    
    
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
			
			$options = array();
			
			$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
			if($routeName == Sitengine_Proto_Backend_Front::ROUTE_GOODIES_UPLOAD) {
				# allow session id to come from GET on uploads from flash application
				$options = array('use_only_cookies' => 0);
			}
			
			$this->getEnv()->startSession($this->getDatabase(), $options);
			$this->getFrontController()->getProtoPackage()->start($this->getDatabase());
			
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
			$this->_templateIndexView = $this->getEnv()->getIncludesDir().'/Sitengine/Proto/Backend/Goodies/_Templates/IndexView.html';
			$this->_templateFormView = $this->getEnv()->getIncludesDir().'/Sitengine/Proto/Backend/Goodies/_Templates/FormView.html';
			$this->_templateUploadView = $this->getEnv()->getIncludesDir().'/Sitengine/Proto/Backend/Goodies/_Templates/UploadView.html';
			$this->_templateAssignView = $this->getEnv()->getIncludesDir().'/Sitengine/Proto/Backend/Goodies/_Templates/AssignView.html';
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
            throw new Sitengine_Proto_Backend_Goodies_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_Proto_Backend_Front &&
    		array_key_exists('config', $invokeArgs) &&
    		$invokeArgs['config'] instanceof Zend_Config
    	)
    	{
    		$this->_env = $invokeArgs['env'];
    		$this->_frontController = $invokeArgs['frontController'];
    		$this->_config = $invokeArgs['config'];
    	}
    	else {
    		require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
    		throw new Sitengine_Proto_Backend_Goodies_Exception('invalid invoke args');
    	}
    }
    
    
    
    protected $_editorSnippet = null;
    public function getEditorSnippet() { return $this->_editorSnippet; }
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	$config = $config->{$this->getFrontController()->getConfigName()}->backend->goodiesController;
    	
    	if(
			isset($config->editorSnippet)
		)
		{
			$this->_editorSnippet = $config->editorSnippet;
		}
		else {
			require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
			throw new Sitengine_Proto_Backend_Goodies_Exception('action controller config error');
		}
    }
    
    
    
    abstract protected function _getEntityModelInstance();
    abstract protected function _getModifierModelInstance();
    abstract protected function _getIndexViewInstance();
    abstract protected function _getFormViewInstance();
    abstract protected function _getUploadViewInstance();
    abstract protected function _getAssignViewInstance();
    
    
    
    
    
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
				$this->getEnv()->getIncludesDir().'/Sitengine/Proto/Backend/Goodies/_Dictionary/en.xml',
				$this->getEnv()->getIncludesDir().'/Sitengine/Proto/Backend/_Dictionary/en.xml'
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
            require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
            throw new Sitengine_Proto_Backend_Goodies_Exception('init error', $exception);
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
    		self::ACTION_INDEX,
    		$this->getFrontController()->getController(Sitengine_Proto_Backend_Front::CONTROLLER_LOGIN)
    	);
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
    		require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
    		throw new Sitengine_Proto_Backend_Goodies_Exception('trying to forward to a non-existing action handler');
    	}
    }
    
    
    
    protected function _getResourceToActionMappings()
    {
    	return array(
    		'default' => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_INDEX
    		),
    		Sitengine_Proto_Backend_Front::ROUTE_GOODIES => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_INDEX
    		),
    		Sitengine_Proto_Backend_Front::ROUTE_GOODIES_NEW => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_INSERT,
    			Sitengine_Env::METHOD_POST => self::ACTION_DOINSERT
    		),
    		Sitengine_Proto_Backend_Front::ROUTE_GOODIES_BATCH => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_INDEX,
    			Sitengine_Env::METHOD_PUT => self::ACTION_DOBATCHUPDATE,
    			Sitengine_Env::METHOD_DELETE => self::ACTION_DOBATCHDELETE
    		),
    		Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHARP => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_UPDATE,
    			Sitengine_Env::METHOD_PUT => self::ACTION_DOUPDATE
    		),
    		Sitengine_Proto_Backend_Front::ROUTE_GOODIES_UPLOAD => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_UPLOAD,
    			Sitengine_Env::METHOD_POST => self::ACTION_DOUPLOAD,
    			Sitengine_Env::METHOD_DELETE => self::ACTION_DOBATCHUNLINK
    		),
    		Sitengine_Proto_Backend_Front::ROUTE_GOODIES_ASSIGN => array(
    			Sitengine_Env::METHOD_GET => self::ACTION_ASSIGN,
    			Sitengine_Env::METHOD_POST => self::ACTION_DOBATCHASSIGN
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
    		require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
    		$exception = new Sitengine_Proto_Backend_Goodies_Exception(
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
    
    
    
    
    protected function _doUpdateAction()
    {
        try {
        	$this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
                throw new Sitengine_Proto_Backend_Goodies_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            /*
            $tables = array(
                $this->getFrontController()->getProtoPackage()->getGoodiesTableName() => 'WRITE'
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
            
            if(is_null($data))
            {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORUPDATE,
                	$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_ERRORUPDATE),
                	true
                );
                return $this->_goToAction(self::ACTION_UPDATE);
            }
            else {
                $this->getEntity()->refresh($data);
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKUPDATE,
                	$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_OKUPDATE),
                	false
                );
            }
            
            $this->getStatus()->save();
            
			$args = array(
				Sitengine_Env::PARAM_ID => $this->getRequest()->get(Sitengine_Env::PARAM_ID)
			);
			
			$query = array(
				Sitengine_Env::PARAM_PAYLOAD_NAME => $this->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME)
			);
			
			$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_SHARP);
			$uri  = $this->getRequest()->getBasePath().'/'.$route->assemble($args);
			$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query, '&');
			$this->getResponse()->setRedirect($uri);
			$this->getResponse()->sendResponse();
			print ' ';
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _doInsertAction()
    {
        try {
        	$this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
            	require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
            	throw new Sitengine_Proto_Backend_Goodies_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            $data = $modifier->insert();
            
            if(is_null($data))
            {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORINSERT,
                	$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_ERRORINSERT),
                	true
                );
                return $this->_goToAction(self::ACTION_INSERT);
            }
            
			$this->getStatus()->set(
				Sitengine_Env::STATUS_OKINSERT,
				$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_OKINSERT),
				false
			);
			
			$this->getStatus()->save();
			
			$query = array(
				Sitengine_Env::PARAM_SORT => 'cdate',
				Sitengine_Env::PARAM_ORDER => 'desc'
			);
			$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES);
			$uri  = $this->getRequest()->getBasePath().'/'.$route->assemble();
			$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query, '&');
			$this->getResponse()->setRedirect($uri);
			$this->getResponse()->sendResponse();
			print ' ';
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _doUploadAction()
    {
    	try {
            $this->_start();
            
            if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                print $this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_UNAUTHORIZED);
				exit;
            }
            
			print $this->_getModifierModelInstance()->uploadToTempDir();
            exit;
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    
    protected function _doBatchDeleteAction()
    {
        try {
        	$this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
            	require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
            	throw new Sitengine_Proto_Backend_Goodies_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            
            $deleted = 0;
            $rows = Sitengine_Controller_Request_Http::getSelectedRows($_POST);
            /*
            $tables = array(
                $this->getFrontController()->getProtoPackage()->getGoodiesTableName() => 'WRITE',
                $this->getFrontController()->getProtoPackage()->getShouldiesTableName() => 'WRITE',
                $this->getFrontController()->getProtoPackage()->getCouldiesTableName() => 'WRITE'
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
                if($deleted < sizeof($rows))
                {
                	$this->getStatus()->set(
                		Sitengine_Env::STATUS_ERRORBATCHTRASH,
                		$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_ERRORBATCHTRASH),
                		true
                	);
                	return $this->_goToAction(self::ACTION_INDEX);
                }
                else if(sizeof($rows) > 0)
                {
                	$this->getStatus()->set(
                		Sitengine_Env::STATUS_OKBATCHTRASH,
                		$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_OKBATCHTRASH),
                		false
                	);
                }
                #$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            }
            
            $this->getStatus()->save();
            
            $query = array(
				Sitengine_Env::PARAM_SORT => $this->getRequest()->get(Sitengine_Env::PARAM_SORT),
				Sitengine_Env::PARAM_ORDER => $this->getRequest()->get(Sitengine_Env::PARAM_ORDER)
			);
			
			$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES);
			$uri  = $this->getRequest()->getBasePath().'/'.$route->assemble();
			$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query, '&');
			$this->getResponse()->setRedirect($uri);
			$this->getResponse()->sendResponse();
			print ' ';
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _doBatchUpdateAction()
    {
        try {
        	$this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
            	require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
            	throw new Sitengine_Proto_Backend_Goodies_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            $updated = 0;
            $rows = Sitengine_Controller_Request_Http::getModifiedRows($_POST);
            /*
            $tables = array(
                $this->getFrontController()->getProtoPackage()->getGoodiesTableName() => 'WRITE'
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
                    $affectedRows = $modifier->updateFromList($id, $data, $this->getFrontController()->getProtoPackage()->getAuthorizedGroups());
                    if($affectedRows > 0) { $updated++; }
                    else { $this->_markedRows[$id] = 1; }
                }
               	#$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
                
                if($updated < sizeof($rows))
                {
                	$this->getStatus()->set(
                		Sitengine_Env::STATUS_ERRORBATCHUPDATE,
                		$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_ERRORBATCHUPDATE),
                		true
                	);
                	return $this->_goToAction(self::ACTION_INDEX);
                }
                else if(sizeof($rows) > 0)
                {
                	$this->getStatus()->set(
                		Sitengine_Env::STATUS_OKBATCHUPDATE,
                		$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_OKBATCHUPDATE),
                		false
                	);
                }
            }
            
            $this->getStatus()->save();
            
            $query = array(
				Sitengine_Env::PARAM_SORT => $this->getRequest()->get(Sitengine_Env::PARAM_SORT),
				Sitengine_Env::PARAM_ORDER => $this->getRequest()->get(Sitengine_Env::PARAM_ORDER)
			);
			
			$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES);
			$uri  = $this->getRequest()->getBasePath().'/'.$route->assemble();
			$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($query, '&');
			$this->getResponse()->setRedirect($uri);
			$this->getResponse()->sendResponse();
			print ' ';
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _doBatchAssignAction()
    {
        try {
            $this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
                throw new Sitengine_Proto_Backend_Goodies_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            $updated = 0;
            $rows = Sitengine_Controller_Request_Http::getModifiedRows($_POST);
            #Sitengine_Debug::print_r($rows);
            
            
            # prepare data
            $gid = $this->getPermiso()->getDirectory()->getGroupId($this->getFrontController()->getProtoPackage()->getOwnerGroup());
			$gid = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
			$date = new Zend_Date();
			$date->setTimezone('UTC');
			$cdate = $date->get(Zend_Date::ISO_8601, Sitengine_Env::LANGUAGE_EN);
            /*
            $tables = array(
                $this->getFrontController()->getProtoPackage()->getGoodiesTableName() => 'WRITE'
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
                	$affectedRows = 0;
                	$filename = (isset($_POST['FILENAME'.$id])) ? $_POST['FILENAME'.$id] : null;
                	
                	if($filename !== null) {
						$data[Sitengine_Permiso::FIELD_GID] = $gid;
						$data['cdate'] = $cdate;
						$data['mdate'] = $cdate;
						$affectedRows = $modifier->assignFromList($filename, $data);
					}
                    if($affectedRows > 0) { $updated++; }
                    else { $this->_markedRows[$id] = 1; }
                }
                #$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
                
                if($updated < sizeof($rows)) {
                    $this->getStatus()->set(
                    	Sitengine_Env::STATUS_ERRORBATCHASSIGN,
                    	$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_ERRORBATCHASSIGN),
                    	true
                    );
                    return $this->_goToAction(self::ACTION_ASSIGN);
                }
                else if(sizeof($rows) > 0) {
                    $this->getStatus()->set(
                    	Sitengine_Env::STATUS_OKBATCHASSIGN,
                    	$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_OKBATCHASSIGN),
                    	false
                    );
                }
            }
            
            $this->getStatus()->save();
			$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_ASSIGN);
			$uri  = $this->getRequest()->getBasePath().'/'.$route->assemble();
			$this->getResponse()->setRedirect($uri);
			$this->getResponse()->sendResponse();
			print ' ';
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _doBatchUnlinkAction()
    {
        try {
            $this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Proto/Backend/Goodies/Exception.php';
                throw new Sitengine_Proto_Backend_Goodies_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $deleted = 0;
            $rows = Sitengine_Controller_Request_Http::getSelectedRows($_POST);
            
            if(sizeof($rows) > 0)
            {
                foreach($rows as $id => $v)
                {
                	$filename = (isset($_POST['FILENAME'.$id])) ? $_POST['FILENAME'.$id] : null;
                	$file = $this->getTempDir().'/'.$filename;
                	if($filename !== null && is_writeable($file) && unlink($file)) { $deleted++; }
                    else { $this->_markedRows[$id] = 1; }
                }
                if($deleted < sizeof($rows)) {
                    $this->getStatus()->set(
                    	Sitengine_Env::STATUS_ERRORBATCHUNLINK,
                    	$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_ERRORBATCHUNLINK),
                    	true
                    );
                    return $this->_goToAction(self::ACTION_ASSIGN);
                }
                else if(sizeof($rows) > 0) {
                    $this->getStatus()->set(
                    	Sitengine_Env::STATUS_OKBATCHUNLINK,
                    	$this->getDictionary()->getFromStatus(Sitengine_Env::STATUS_OKBATCHUNLINK),
                    	false
                    );
                }
            }
   			
   			$this->getStatus()->save();
			$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Proto_Backend_Front::ROUTE_GOODIES_ASSIGN);
			$uri  = $this->getRequest()->getBasePath().'/'.$route->assemble();
			$this->getResponse()->setRedirect($uri);
			$this->getResponse()->sendResponse();
			print ' ';
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    protected function _indexAction()
    {
    	try {
    		$this->_start();
			if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
				return $this->_forwardToLogin();
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
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _insertAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            $view = $this->_getFormViewInstance();
			$view->setInputMode(Sitengine_Env::INPUTMODE_INSERT);
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateFormView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
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
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
			if(!$this->getEntity()->start()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getFormViewInstance();
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateFormView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
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
    
    
    
    
    protected function _uploadAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
			$view = $this->_getUploadViewInstance();
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateUploadView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateUploadView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _assignAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getProtoPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
			$view = $this->_getAssignViewInstance();
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateAssignView));
    		$view->doctype()->setDoctype('XHTML1_STRICT');
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateAssignView));
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