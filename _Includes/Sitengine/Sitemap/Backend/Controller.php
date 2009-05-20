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
 * @package    Sitengine_Sitemap
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Controller/Action.php';


abstract class Sitengine_Sitemap_Backend_Controller extends Sitengine_Controller_Action
{
    
    const ACTION_INDEX = 'index';
    const ACTION_SEARCH = 'search';
    const ACTION_UPDATEFILE = 'updateFile';
    const ACTION_UPDATEPAGE = 'updatePage';
    const ACTION_UPDATEMASK = 'updateMask';
    const ACTION_UPDATELAYER = 'updateLayer';
    const ACTION_UPDATESNIPPET = 'updateSnippet';
    const ACTION_NEWFILE = 'newFile';
    const ACTION_NEWPAGE = 'newPage';
    const ACTION_NEWMASK = 'newMask';
    const ACTION_NEWLAYER = 'newLayer';
    const ACTION_NEWSNIPPET = 'newSnippet';
    const ACTION_DOUPDATEFILE = 'doUpdateFile';
    const ACTION_DOUPDATEPAGE = 'doUpdatePage';
    const ACTION_DOUPDATEMASK = 'doUpdateMask';
    const ACTION_DOUPDATELAYER = 'doUpdateLayer';
    const ACTION_DOUPDATESNIPPET = 'doUpdateSnippet';
    const ACTION_DONEWFILE = 'doNewFile';
    const ACTION_DONEWPAGE = 'doNewPage';
    const ACTION_DONEWMASK = 'doNewMask';
    const ACTION_DONEWLAYER = 'doNewLayer';
    const ACTION_DONEWSNIPPET = 'doNewSnippet';
    const ACTION_DOBATCHDELETE = 'doBatchDelete';
    const ACTION_DOBATCHUPDATE = 'doBatchUpdate';
    const PARAM_FILTER_RESET = 'resetFilter';
    const PARAM_FILTER_BY_TYPE = 'filterByType';
    const PARAM_FILTER_BY_FIND = 'find';
    const PARAM_SEARCH_RESET = 'resetSearch';
    const PARAM_SEARCH_BY_TYPE = 'searchByType';
    const PARAM_SEARCH_BY_FIND = 'searchByFind';
    #const PARAM_SETTINGS_RESET = 'resetSettings';
    const VALUE_NONESELECTED = 'noneSelected';
    #const ERROR_BAD_REQUEST = -1;
    #const ERROR_NOT_FOUND = -2;
    
    protected $_started = false;
    protected $_config = null;
    protected $_env = null;
    #protected $_package = null;
    protected $_logger = null;
    protected $_database = null;
    protected $_status = null;
    protected $_preferences = null;
    protected $_locale = null;
    protected $_permiso = null;
    protected $_namespace = null;
    protected $_translate = null;
    protected $_transcripts = null;
    protected $_entity = null;
    protected $_markedRows = array();
    protected $_templateIndexView = null;
    protected $_templateSearchView = null;
    protected $_templateFileFormView = null;
    protected $_templatePageFormView = null;
    protected $_templateMaskFormView = null;
    protected $_templateLayerFormView = null;
    protected $_templateSnippetFormView = null;
    
    public function getEnv() { return $this->_env; }
    #public function getPackage() { return $this->_package; }
    public function getLogger() { return $this->_logger; }
    public function getDatabase() { return $this->_database; }
    public function getStatus() { return $this->_status; }
    public function getPreferences() { return $this->_preferences; }
    public function getLocale() { return $this->_locale; }
    public function getPermiso() { return $this->_permiso; }
    public function getNamespace() { return $this->_namespace; }
    public function getTranslate() { return $this->_translate; }
    public function getTranscripts() { return $this->_transcripts; }
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
    
    
    
    
    private $_record = null;
    
    public function getRecord()
    {
    	if($this->_record === null) {
    		$this->_record = $this->_getRecordModelInstance();
    	}
    	return $this->_record;
    }
    
    abstract protected function _getRecordModelInstance();
    
    
	
	# properties loaded from config
    protected $_ownerGroup = null;
    protected $_authorizedGroups = array();
    
    public function getOwnerGroup() { return $this->_ownerGroup; }
	public function getAuthorizedGroups() { return $this->_authorizedGroups; }
    
    
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
        	$this->_transcripts = $this->_getTranscriptsInstance();
        	$this->_entity = $this->_getEntityModelInstance();
			require_once 'Zend/Session/Namespace.php';
    		$this->_namespace = new Zend_Session_Namespace(get_class($this));
			
			$this->_templateIndexView = $this->getEnv()->getIncludesDir().'/Sitengine/Sitemap/Backend/_Templates/IndexView.html';
			$this->_templateSearchView = $this->getEnv()->getIncludesDir().'/Sitengine/Sitemap/Backend/_Templates/SearchView.html';
			$this->_templateFileFormView = $this->getEnv()->getIncludesDir().'/Sitengine/Sitemap/Backend/_Templates/FileFormView.html';
			$this->_templatePageFormView = $this->getEnv()->getIncludesDir().'/Sitengine/Sitemap/Backend/_Templates/PageFormView.html';
			$this->_templateMaskFormView = $this->getEnv()->getIncludesDir().'/Sitengine/Sitemap/Backend/_Templates/MaskFormView.html';
			$this->_templateLayerFormView = $this->getEnv()->getIncludesDir().'/Sitengine/Sitemap/Backend/_Templates/LayerFormView.html';
			$this->_templateSnippetFormView = $this->getEnv()->getIncludesDir().'/Sitengine/Sitemap/Backend/_Templates/SnippetFormView.html';
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Sitemap/Backend/Exception.php';
            throw new Sitengine_Sitemap_Backend_Exception('object instantiation error', $exception);
        }
    }
    
    
    
    protected function _mapInvokeArgs(array $invokeArgs)
    {
    	if(
    		array_key_exists('env', $invokeArgs) &&
    		$invokeArgs['env'] instanceof Sitengine_Env_Default &&
    		#array_key_exists('package', $invokeArgs) &&
    		#$invokeArgs['package'] instanceof Sitengine_Sitemap &&
    		array_key_exists('frontController', $invokeArgs) &&
    		$invokeArgs['frontController'] instanceof Sitengine_Sitemap_Backend_Front &&
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
    		require_once 'Sitengine/Sitemap/Backend/Exception.php';
    		throw new Sitengine_Sitemap_Backend_Exception('invalid invoke args');
    	}
    }
    
    
    
    
    
    protected $_editorSnippet = null;
    public function getEditorSnippet() { return $this->_editorSnippet; }
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	$config = $config->sitemapBackendIndexController;
    	
    	if(
    		isset($config->editorSnippet) &&
			isset($config->ownerGroup) &&
			isset($config->authorizedGroups)
		)
		{
			$this->_editorSnippet = $config->editorSnippet;
			$this->_ownerGroup = $config->ownerGroup;
			$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/Sitemap/Backend/Exception.php';
			throw new Sitengine_Sitemap_Backend_Exception('action controller config error');
		}
    }
    
    
    abstract protected function _getEntityModelInstance();
    abstract protected function _getModifierModelInstance();
    abstract protected function _getFileFormViewInstance();
    abstract protected function _getPageFormViewInstance();
    abstract protected function _getMaskFormViewInstance();
    abstract protected function _getLayerFormViewInstance();
    abstract protected function _getSnippetFormViewInstance();
    abstract protected function _getIndexViewInstance();
    abstract protected function _getSearchViewInstance();
    
    
    
    
    protected function _getTranscriptsInstance()
    {
    	require_once 'Sitengine/Transcripts.php';
    	return new Sitengine_Transcripts(
    		array(
    			Sitengine_Env::LANGUAGE_EN,
    			#Sitengine_Env::LANGUAGE_DE,
    			#Sitengine_Env::LANGUAGE_FR
    		)
    	);
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
			$this->getEnv()->getIncludesDir().'/Sitengine/Env/_Dictionary/de.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/Sitemap/Backend/_Dictionary/de.xml'
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
				if($this->getStatus()->getCode() != Sitengine_Env::STATUS_OKINSERT) {
					$this->getStatus()->reset();
				}
			}
		}
        catch (Exception $exception) {
            if($exception->getMessage() == $errorOrgNotFound) {
        		throw $this->_prepareErrorHandler($exception);
        	}
            require_once 'Sitengine/Sitemap/Backend/Exception.php';
            throw new Sitengine_Sitemap_Backend_Exception('init error', $exception);
        }
    }
    
    
    
    protected function _setSelfSubmitUri()
    {
    	/*
    	$args = array(
			Sitengine_Env::PARAM_ACTION => $this->getRequest()->getActionName()
		);
		$route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
		$uriSelfSubmit = $this->getRequest()->getBasePath().'/'.$route->assemble($args, true);
		#$this->getEnv()->setUriSelfSubmit($uriSelfSubmit);
		*/
		$uriSelfSubmit = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		#$this->getEnv()->setUriSelfSubmit($uriSelfSubmit);
    }
    
    
    
    protected function _forwardToLogin()
    {
    	$target = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		$this->getRequest()->setParam(Sitengine_Env::PARAM_TARGET, $target);
    	
    	$this->_forward(
    		self::ACTION_INDEX,
    		$this->getFrontController()->getController(Sitengine_Sitemap_Backend_Front::CONTROLLER_LOGIN)
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
    		require_once 'Sitengine/Sitemap/Backend/Exception.php';
    		throw new Sitengine_Sitemap_Backend_Exception('trying to forward to a non-existing action handler');
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
    
    
    
    protected function _startEntity()
    {
        try {
            if(!$this->getEntity()->start(
                    $this->getRequest()->get(Sitengine_Env::PARAM_ID),
                    $this->getRequest()->get(Sitengine_Env::PARAM_PARENTID)
                )
            ) { return false; }
            
            $subject = $this->getEntity()->getData();
            
            if(sizeof($subject))
            {
                if(
                    $subject['type']==Sitengine_Sitemap::ITEMTYPE_MASK &&
                    $this->getRequest()->getActionName()!=self::ACTION_UPDATEMASK &&
					$this->getRequest()->getActionName()!=self::ACTION_DOUPDATEMASK &&
                    $this->getRequest()->getActionName()!=self::ACTION_INDEX &&
					$this->getRequest()->getActionName()!=self::ACTION_NEWFILE &&
					$this->getRequest()->getActionName()!=self::ACTION_DONEWFILE &&
					$this->getRequest()->getActionName()!=self::ACTION_UPDATEFILE &&
					$this->getRequest()->getActionName()!=self::ACTION_DOUPDATEFILE &&
					$this->getRequest()->getActionName()!=self::ACTION_NEWSNIPPET &&
					$this->getRequest()->getActionName()!=self::ACTION_DONEWSNIPPET &&
					$this->getRequest()->getActionName()!=self::ACTION_UPDATESNIPPET &&
					$this->getRequest()->getActionName()!=self::ACTION_DOUPDATESNIPPET &&
					$this->getRequest()->getActionName()!=self::ACTION_DOBATCHDELETE &&
					$this->getRequest()->getActionName()!=self::ACTION_DOBATCHUPDATE
                ) {
                	require_once 'Sitengine/Sitemap/Backend/Exception.php';
                	throw new Sitengine_Sitemap_Backend_Exception(
                		'invalid action on child element of a mask',
                		Sitengine_Env::ERROR_BAD_REQUEST
                	);
                }
                if(
                    $subject['type']==Sitengine_Sitemap::ITEMTYPE_PAGE &&
                    $this->getRequest()->getActionName()!=self::ACTION_UPDATEPAGE &&
					$this->getRequest()->getActionName()!=self::ACTION_DOUPDATEPAGE &&
                    $this->getRequest()->getActionName()!=self::ACTION_INDEX &&
					$this->getRequest()->getActionName()!=self::ACTION_NEWFILE &&
					$this->getRequest()->getActionName()!=self::ACTION_DONEWFILE &&
					$this->getRequest()->getActionName()!=self::ACTION_UPDATEFILE &&
					$this->getRequest()->getActionName()!=self::ACTION_DOUPDATEFILE &&
					$this->getRequest()->getActionName()!=self::ACTION_NEWSNIPPET &&
					$this->getRequest()->getActionName()!=self::ACTION_DONEWSNIPPET &&
					$this->getRequest()->getActionName()!=self::ACTION_UPDATESNIPPET &&
					$this->getRequest()->getActionName()!=self::ACTION_DOUPDATESNIPPET &&
					$this->getRequest()->getActionName()!=self::ACTION_DOBATCHDELETE &&
					$this->getRequest()->getActionName()!=self::ACTION_DOBATCHUPDATE
                ) {
                	require_once 'Sitengine/Sitemap/Backend/Exception.php';
                	throw new Sitengine_Sitemap_Backend_Exception(
                		'invalid action on child element of a page',
                		Sitengine_Env::ERROR_BAD_REQUEST
                	);
                }
                if(
                    $subject['type']==Sitengine_Sitemap::ITEMTYPE_FILE &&
                    $this->getRequest()->getActionName()!=self::ACTION_UPDATEFILE &&
					$this->getRequest()->getActionName()!=self::ACTION_DOUPDATEFILE
                ) {
                	require_once 'Sitengine/Sitemap/Backend/Exception.php';
                	throw new Sitengine_Sitemap_Backend_Exception(
                		'invalid action on child element of a file',
                		Sitengine_Env::ERROR_BAD_REQUEST
                	);
                }
                if(
                    $subject['type']==Sitengine_Sitemap::ITEMTYPE_SNIPPET &&
                    $this->getRequest()->getActionName()!=self::ACTION_UPDATESNIPPET &&
					$this->getRequest()->getActionName()!=self::ACTION_DOUPDATESNIPPET
                ) {
                	require_once 'Sitengine/Sitemap/Backend/Exception.php';
                	throw new Sitengine_Sitemap_Backend_Exception(
                		'invalid action on child element of a snippet',
                		Sitengine_Env::ERROR_BAD_REQUEST
                	);
                }
            }
            return true;
        }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    public function binAction()
    {
    	try {
			$this->_start();
			
			if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
				return $this->_forwardToLogin();
			}
			
			$fileId = $this->getRequest()->get(Sitengine_Env::PARAM_FILE);
			
			switch($fileId) {
				case 'file1Original': $dir = $this->_file1OriginalDir; break;
				case 'file1Thumbnail': $dir = $this->_file1ThumbnailDir; break;
				default: $dir = null;
			}
			
			if($dir === null) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('invalid file id');
			}
			
			$file = $this->getEntity()->getFile(
				$this->getRequest()->get(Sitengine_Env::PARAM_ID),
				$fileId
			);
			
			$path = $dir.'/'.$file[$fileId.'Name'];
			
			if(!is_readable($path)) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('file could not be read');
			}
			
			#$this->getResponse()->setHeader('Cache-control', 'no-cache');
			$this->getResponse()->setHeader('Content-type', $file[$fileId.'Mime']);
			$this->getResponse()->setHeader('Content-Disposition', 'inline; filename="'.$file[$fileId.'Name'].'"');
			$this->getResponse()->sendResponse();
			readfile($path);
			return $this->_response;
		}
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doUpdateFileAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            /*
            $tables = array(
                $this->getFrontController()->getSitemapPackage()->getTableSitemap() => 'WRITE'
            );
            # lock tables
            require_once 'Sitengine/Sql.php';
            $q = Sitengine_Sql::getLockQuery($tables);
            $this->getDatabase()->getConnection()->exec($q);
            */
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $modifier = $this->_getModifierModelInstance();
            $data = $modifier->update(
                Sitengine_Sitemap::ITEMTYPE_FILE,
                $this->getRequest()->get(Sitengine_Env::PARAM_ID),
                $this->getEntity()->getData()
            );
            #$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORUPDATE),
                	true
                );
                return $this->_goToAction(self::ACTION_UPDATEFILE);
            }
            else {
                $this->getEntity()->refreshData($data);
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKUPDATE),
                	false
                );
                return $this->_goToAction(self::ACTION_UPDATEFILE);
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doUpdatePageAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            /*
            $tables = array(
                $this->getFrontController()->getSitemapPackage()->getTableSitemap() => 'WRITE'
            );
            # lock tables
            require_once 'Sitengine/Sql.php';
            $q = Sitengine_Sql::getLockQuery($tables);
            $this->getDatabase()->getConnection()->exec($q);
            */
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $modifier = $this->_getModifierModelInstance();
            $data = $modifier->update(
                Sitengine_Sitemap::ITEMTYPE_PAGE,
                $this->getRequest()->get(Sitengine_Env::PARAM_ID),
                $this->getEntity()->getData()
            );
            #$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORUPDATE),
                	true
                );
                return $this->_goToAction(self::ACTION_UPDATEPAGE);
            }
            else {
                $this->getEntity()->refreshData($data);
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKUPDATE),
                	false
                );
                return $this->_goToAction(self::ACTION_UPDATEPAGE);
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    public function doUpdateMaskAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            /*
            $tables = array(
                $this->getFrontController()->getSitemapPackage()->getTableSitemap() => 'WRITE'
            );
            # lock tables
            require_once 'Sitengine/Sql.php';
            $q = Sitengine_Sql::getLockQuery($tables);
            $this->getDatabase()->getConnection()->exec($q);
            */
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $modifier = $this->_getModifierModelInstance();
            $data = $modifier->update(
                Sitengine_Sitemap::ITEMTYPE_MASK,
                $this->getRequest()->get(Sitengine_Env::PARAM_ID),
                $this->getEntity()->getData()
            );
            #$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORUPDATE),
                	true
                );
                return $this->_goToAction(self::ACTION_UPDATEMASK);
            }
            else {
                $this->getEntity()->refreshData($data);
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKUPDATE),
                	false
                );
                return $this->_goToAction(self::ACTION_UPDATEMASK);
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doUpdateLayerAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            /*
            $tables = array(
                $this->getFrontController()->getSitemapPackage()->getTableSitemap() => 'WRITE'
            );
            # lock tables
            require_once 'Sitengine/Sql.php';
            $q = Sitengine_Sql::getLockQuery($tables);
            $this->getDatabase()->getConnection()->exec($q);
            */
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $modifier = $this->_getModifierModelInstance();
            $data = $modifier->update(
                Sitengine_Sitemap::ITEMTYPE_LAYER,
                $this->getRequest()->get(Sitengine_Env::PARAM_ID),
                $this->getEntity()->getData()
            );
            #$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORUPDATE),
                	true
                );
                return $this->_goToAction(self::ACTION_UPDATELAYER);
            }
            else {
                $this->getEntity()->refreshData($data);
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKUPDATE),
                	false
                );
                return $this->_goToAction(self::ACTION_UPDATELAYER);
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doUpdateSnippetAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            /*
            $tables = array(
                $this->getFrontController()->getSitemapPackage()->getTableSitemap() => 'WRITE'
            );
            # lock tables
            require_once 'Sitengine/Sql.php';
            $q = Sitengine_Sql::getLockQuery($tables);
            $this->getDatabase()->getConnection()->exec($q);
            */
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $modifier = $this->_getModifierModelInstance();
            $data = $modifier->update(
                Sitengine_Sitemap::ITEMTYPE_SNIPPET,
                $this->getRequest()->get(Sitengine_Env::PARAM_ID),
                $this->getEntity()->getData()
            );
            #$this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORUPDATE),
                	true
                );
                return $this->_goToAction(self::ACTION_UPDATESNIPPET);
            }
            else {
                $this->getEntity()->refreshData($data);
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKUPDATE),
                	false
                );
                return $this->_goToAction(self::ACTION_UPDATESNIPPET);
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doNewFileAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $data = $modifier->insert(
                Sitengine_Sitemap::ITEMTYPE_FILE,
                $this->getEntity()->getParentId()
            );
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORINSERT),
                	true
                );
                return $this->_goToAction(self::ACTION_NEWFILE);
            }
            else {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKINSERT),
                	false
                );
                $this->getStatus()->save();
                
                # avoid double submits
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => self::ACTION_INDEX,
                    Sitengine_Env::PARAM_PARENTID => $this->getEntity()->getParentId(),
                    Sitengine_Env::PARAM_SORT => 'cdate',
                    Sitengine_Env::PARAM_ORDER => 'desc'
                );
                $route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $uri = $this->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $this->getResponse()->setRedirect($uri);
                $this->getResponse()->sendResponse();
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doNewPageAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $data = $modifier->insert(
                Sitengine_Sitemap::ITEMTYPE_PAGE,
                $this->getEntity()->getParentId()
            );
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORINSERT),
                	true
                );
                return $this->_goToAction(self::ACTION_NEWPAGE);
            }
            else {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKINSERT),
                	false
                );
                $this->getStatus()->save();
                
                # avoid double submits
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => self::ACTION_INDEX,
                    Sitengine_Env::PARAM_PARENTID => $this->getEntity()->getParentId(),
                    Sitengine_Env::PARAM_SORT => 'cdate',
                    Sitengine_Env::PARAM_ORDER => 'desc'
                );
                $route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $uri = $this->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $this->getResponse()->setRedirect($uri);
                $this->getResponse()->sendResponse();
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doNewMaskAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $data = $modifier->insert(
                Sitengine_Sitemap::ITEMTYPE_MASK,
                $this->getEntity()->getParentId()
            );
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORINSERT),
                	true
                );
                return $this->_goToAction(self::ACTION_NEWMASK);
            }
            else {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKINSERT),
                	false
                );
                $this->getStatus()->save();
                
                # avoid double submits
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => self::ACTION_INDEX,
                    Sitengine_Env::PARAM_PARENTID => $this->getEntity()->getParentId(),
                    Sitengine_Env::PARAM_SORT => 'cdate',
                    Sitengine_Env::PARAM_ORDER => 'desc'
                );
                $route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $uri = $this->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $this->getResponse()->setRedirect($uri);
                $this->getResponse()->sendResponse();
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doNewLayerAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $data = $modifier->insert(
                Sitengine_Sitemap::ITEMTYPE_LAYER,
                $this->getEntity()->getParentId()
            );
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORINSERT),
                	true
                );
                return $this->_goToAction(self::ACTION_NEWLAYER);
            }
            else {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKINSERT),
                	false
                );
                $this->getStatus()->save();
                
                # avoid double submits
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => self::ACTION_INDEX,
                    Sitengine_Env::PARAM_PARENTID => $this->getEntity()->getParentId(),
                    Sitengine_Env::PARAM_SORT => 'cdate',
                    Sitengine_Env::PARAM_ORDER => 'desc'
                );
                $route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $uri = $this->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $this->getResponse()->setRedirect($uri);
                $this->getResponse()->sendResponse();
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doNewSnippetAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $data = $modifier->insert(
                Sitengine_Sitemap::ITEMTYPE_SNIPPET,
                $this->getEntity()->getParentId()
            );
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORINSERT),
                	true
                );
                return $this->_goToAction(self::ACTION_NEWSNIPPET);
            }
            else {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKINSERT,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKINSERT),
                	false
                );
                $this->getStatus()->save();
                
                # avoid double submits
                $args = array(
                    #Sitengine_Env::PARAM_ORG => $this->getPermiso()->getOrganization()->getNameNoDefault(),
                    Sitengine_Env::PARAM_ACTION => self::ACTION_UPDATESNIPPET,
                    Sitengine_Env::PARAM_ID => $data['id']
                );
                $route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Sitemap_Backend_Front::ROUTE_INDEX);
                $uri = $this->getRequest()->getBasePath().'/'.$route->assemble($args, true);
                $this->getResponse()->setRedirect($uri);
                $this->getResponse()->sendResponse();
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function doBatchDeleteAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $modifier = $this->_getModifierModelInstance();
            $deleted = 0;
            $rows = Sitengine_Controller_Request_Http::getSelectedRows($_POST);
            /*
            $tables = array(
                $this->getFrontController()->getSitemapPackage()->getTableSitemap() => 'WRITE'
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
    
    
    
    public function doBatchUpdateAction()
    {
        try {
            $this->_start();
        	
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Sitemap/Backend/Exception.php';
                throw new Sitengine_Sitemap_Backend_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			
            $modifier = $this->_getModifierModelInstance();
            $updated = 0;
            $rows = Sitengine_Controller_Request_Http::getModifiedRows($_POST);
            /*
            $tables = array(
                $this->getFrontController()->getSitemapPackage()->getTableSitemap() => 'WRITE'
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
                    $affectedRows = $modifier->updateFromList($id, $data, $this->_authorizedGroups);
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
    
    
    
    public function indexAction()
    {
    	try {
    		$this->_start();
			if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
				return $this->_forwardToLogin();
			}
			if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getIndexViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
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
    
    
    
    
    public function searchAction()
    {
    	try {
    		$this->_start();
			if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getSearchViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateSearchView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateSearchView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
		}
		catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function newFileAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getFileFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateFileFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateFileFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function updateFileAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
			if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getFileFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateFileFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateFileFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    public function newPageAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getPageFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templatePageFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templatePageFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function updatePageAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
			if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getPageFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templatePageFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templatePageFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function newMaskAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getMaskFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateMaskFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateMaskFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function updateMaskAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
			if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getMaskFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateMaskFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateMaskFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function newLayerAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getLayerFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateLayerFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateLayerFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function updateLayerAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
			if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getLayerFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateLayerFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateLayerFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function newSnippetAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
            if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getSnippetFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateSnippetFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateSnippetFormView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    public function updateSnippetAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->_authorizedGroups)) {
                return $this->_forwardToLogin();
            }
			if(!$this->_startEntity()) {
				return $this->_forwardToLogin();
			}
			$view = $this->_getSnippetFormViewInstance();
			$view->controller = $this;
			$view->env = $this->getEnv();
			$view->frontController = $this->getFrontController();
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateSnippetFormView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateSnippetFormView));
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