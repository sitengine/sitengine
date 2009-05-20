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


abstract class Sitengine_Blog_Frontend_Blogs_Posts_Files_Controller extends Sitengine_Controller_Action
{
    
	#const ACTION_BIN = 'bin';
    const ACTION_INDEX = '_index';
    const ACTION_VIEW = '_view';
    #const ACTION_INSERT = '_insert';
    #const ACTION_UPLOAD = '_upload';
    #const ACTION_ASSIGN = '_assign';
    #const ACTION_DOWNLOAD = 'download';
    #const ACTION_DOUPDATE = '_doUpdate';
    #const ACTION_DOINSERT = '_doInsert';
    #const ACTION_DOUPLOAD = '_doUpload';
    #const ACTION_DOBATCHDELETE = '_doBatchDelete';
    #const ACTION_DOBATCHUPDATE = '_doBatchUpdate';
    #const ACTION_DOBATCHASSIGN = '_doBatchAssign';
    #const ACTION_DOBATCHUNLINK = '_doBatchUnlink';
    const PARAM_FILTER_RESET = 'resetFilter';
    const PARAM_FILTER_BY_FIND = 'find';
    const PARAM_FILTER_BY_TYPE = 'filterByType';
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
    protected $_templateDetailView = null;
    #protected $_templateUploadView = null;
    #protected $_templateAssignView = null;
    
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
    #public function getMarkedRows() { return $this->_markedRows; }
	
    
    
   /*
    public function getTempDir()
    {
    	$this->_start();
    	if(!$this->getFrontController()->getBlogPackage()->tempDirPerUser()) { return $this->getFrontController()->getBlogPackage()->getFileTempDir(); }
        else {
        	$userTempDir = $this->getFrontController()->getBlogPackage()->getFileTempDir().'/'.$this->getPermiso()->getAuth()->getId();
			if(!is_dir($userTempDir)) { mkdir($userTempDir, 0777); }
			return $userTempDir;
		}
    }
    */
    
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
			
			$options = array();
			/*
			$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
			if($routeName == Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_UPLOAD) {
				# allow session id to come from GET on uploads from flash application
				$options = array('use_only_cookies' => 0);
			}
			*/
			$this->getEnv()->startSession($this->getDatabase(), $options);
			$this->getFrontController()->getBlogPackage()->start($this->getDatabase());
			
			require_once 'Sitengine/Status.php';
			$this->_status = Sitengine_Status::getInstance();
			require_once 'Sitengine/Env/Preferences.php';
			$this->_preferences = Sitengine_Env_Preferences::getInstance();
			$this->_locale = $this->getEnv()->getLocaleInstance();
			$this->_permiso = $this->getFrontController()->getPermiso()->start($this->getDatabase());
        	$this->_translate = $this->_getTranslateInstance();
        	$this->_entity = $this->_getEntityModelInstance();
			require_once 'Zend/Session/Namespace.php';
    		$this->_namespace = new Zend_Session_Namespace(get_class($this));
			$this->_templateIndexView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/Files/_Templates/IndexView.html';
			$this->_templateDetailView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/Files/_Templates/DetailView.html';
			#$this->_templateUploadView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/Files/_Templates/UploadView.html';
			#$this->_templateAssignView = $this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/Files/_Templates/AssignView.html';
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('object instantiation error', $exception);
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
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('invalid invoke args');
    	}
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	/*
    	$config = $config->{$this->getFrontController()->getConfigName()}->frontend->blogsPostsFilesController;
    	
    	if(
			#isset($config->ownerGroup) &&
			isset($config->authorizedGroups)
		)
		{
			#$this->_ownerGroup = $config->ownerGroup;
			$this->_authorizedGroups = $config->authorizedGroups->toArray();
		}
		else {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('action controller config error');
		}
		*/
    }
    
    
    
    abstract protected function _getEntityModelInstance();
    #abstract protected function _getModifierModelInstance();
    abstract protected function _getIndexViewInstance();
    abstract protected function _getDetailViewInstance();
    #abstract protected function _getUploadViewInstance();
    #abstract protected function _getAssignViewInstance();
    
    
    
    
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
			$this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/_Dictionary/en.xml',
			$this->getEnv()->getIncludesDir().'/Sitengine/Blog/Frontend/Blogs/Posts/Files/_Dictionary/en.xml'
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
            require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
            throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('init error', $exception);
        }
    }
    
    
    
    protected function _setSelfSubmitUri()
    {
    	$uriSelfSubmit = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
		#$this->getEnv()->setUriSelfSubmit($uriSelfSubmit);
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
    		#$this->_setSelfSubmitUri();
    		call_user_func(array($this, $handler));
    	}
    	else {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
    		throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('trying to forward to a non-existing action handler');
    	}
    }
    
    
    
    public function restMapperAction()
    {
    	$routeName = $this->getFrontController()->getRouter()->getCurrentRouteName();
    	$method = $this->getRequest()->getIntendedMethod();
    	$action = null;
    	#print $routeName;
    	#print $method;
    	switch($routeName)
    	{
    		/*
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INDEX; break;
    			}
    			break;
    		}
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_NEW:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INSERT; break;
    				case Sitengine_Env::METHOD_POST: $action = self::ACTION_DOINSERT; break;
    			}
    			break;
    		}
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_BATCH:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_INDEX; break;
    				case Sitengine_Env::METHOD_PUT: $action = self::ACTION_DOBATCHUPDATE; break;
    				case Sitengine_Env::METHOD_DELETE: $action = self::ACTION_DOBATCHDELETE; break;
    			}
    			break;
    		}
    		*/
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_SHARP:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_VIEW; break;
    				#case Sitengine_Env::METHOD_PUT: $action = self::ACTION_DOUPDATE; break;
    			}
    			break;
    		}
    		/*
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_UPLOAD:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_UPLOAD; break;
    				case Sitengine_Env::METHOD_POST: $action = self::ACTION_DOUPLOAD; break;
    				case Sitengine_Env::METHOD_DELETE: $action = self::ACTION_DOBATCHUNLINK; break;
    			}
    			break;
    		}
    		case Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES_ASSIGN:
    		{
    			switch($method) {
    				case Sitengine_Env::METHOD_GET: $action = self::ACTION_ASSIGN; break;
    				case Sitengine_Env::METHOD_POST: $action = self::ACTION_DOBATCHASSIGN; break;
    			}
    			break;
    		}
    		*/
    	}
    	if($action === null) {
    		require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
    		$exception = new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception(
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
    
    
    
    protected function _startEntity()
    {
    	if(!$this->getEntity()->start()) {
			return $this->_forwardToLogin();
		}
		$type = $this->getEntity()->getAncestorType();
		
		if(
			#$type != Sitengine_Blog_Posts_Table::TYPE_TEXT &&
			$type != Sitengine_Blog_Posts_Table::TYPE_GALLERY
		) {
			require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
			throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('extra files are not supported on '.$type.' posts');
		}
		return true;
    }
    
    
    /*
    protected function _doUpdateAction()
    {
        try {
            $this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            $tables = array(
                $this->getFrontController()->getBlogPackage()->getBlogsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getPostsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getFilesTableName() => 'WRITE'
            );
            # lock tables
            require_once 'Sitengine/Sql.php';
            $q = Sitengine_Sql::getLockQuery($tables);
            $this->getDatabase()->getConnection()->exec($q);
            
            if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
            
            $data = $modifier->update(
                $this->getRequest()->get(Sitengine_Env::PARAM_ID),
                $this->getEntity()->getData()
            );
            $this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            
            if(is_null($data)) {
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_ERRORUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORUPDATE),
                	true
                );
                return $this->_goToAction(self::ACTION_VIEW);
            }
            else {
                $this->getEntity()->refreshData($data);
                $this->getStatus()->set(
                	Sitengine_Env::STATUS_OKUPDATE,
                	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKUPDATE),
                	false
                );
                return $this->_goToAction(self::ACTION_VIEW);
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
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            $modifier = $this->_getModifierModelInstance();
            $tables = array(
                $this->getFrontController()->getBlogPackage()->getBlogsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getPostsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getFilesTableName() => 'WRITE'
            );
            # lock tables
            require_once 'Sitengine/Sql.php';
            $q = Sitengine_Sql::getLockQuery($tables);
            $this->getDatabase()->getConnection()->exec($q);
            
            if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
            
            $data = $modifier->insert(
                $this->getEntity()->getAncestorId()
            );
            $this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
            
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
                $path = array(
                    Sitengine_Env::PARAM_GREATANCESTORID => $this->getEntity()->getGreatAncestorSlug(),
                    Sitengine_Env::PARAM_ANCESTORID => $this->getEntity()->getAncestorId()
                );
                $args = array(
                    Sitengine_Env::PARAM_SORT => 'cdate',
                    Sitengine_Env::PARAM_ORDER => 'desc'
                );
                $route = $this->getFrontController()->getRouter()->getRoute(Sitengine_Blog_Frontend_Front::ROUTE_BLOGS_POSTS_FILES);
                $uri  = $this->getRequest()->getBasePath().'/'.$route->assemble($path, true);
                $uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($args, '&');
                $this->getResponse()->setRedirect($uri);
                $this->getResponse()->sendResponse();
            }
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _doUploadAction()
    {
        try {
            $this->_start();
            
            if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                print $this->getTranslate()->translate(Sitengine_Env::STATUS_FORBIDDEN);
				exit;
            }
            
            /*
            # php setting to play with:
            print 'upload_tmp_dir: '.ini_get('upload_tmp_dir').'<br />'; # PHP_INI_SYSTEM
			print 'file_uploads: '.ini_get('file_uploads').'<br />'; # PHP_INI_SYSTEM
			print 'upload_max_filesize: '.ini_get('upload_max_filesize').'<br />'; # PHP_INI_PERDIR
			print 'post_max_size: '.ini_get('post_max_size').'<br />'; # PHP_INI_PERDIR
			print 'max_input_time: '.ini_get('max_input_time').'<br />'; # PHP_INI_PERDIR
			print 'memory_limit: '.ini_get('memory_limit').'<br />'; # PHP_INI_ALL
			print 'max_execution_time: '.ini_get('max_execution_time').'<br />'; # PHP_INI_ALL
			
			# .htaccess php settings
			php_value upload_max_filesize 666M
			php_value post_max_size 777M
			php_value max_input_time 888
			php_value memory_limit 999
			
			# .htaccess apache settings
			LimitRequestBody
			
			# apache settings
			TimeOut
			
			
			ini_set('max_execution_time', 10000);
            
            if(!isset($_FILES['Filedata'])) {
            	# no file - maybe filesize is greater than post_max_size directive in php.ini
            	print $this->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
            	exit;
			}
			
            $filename = preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES['Filedata']['name']);
            $path = $this->getTempDir().'/'.$filename;
			
			if(file_exists($path) )
			{
				print $this->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_EXISTS);
				exit;
			}
			else if(is_uploaded_file($_FILES['Filedata']['tmp_name']))
			{
				if(move_uploaded_file($_FILES['Filedata']['tmp_name'], $path))
				{
					@chmod($path, 0666);
					print 'OK'; exit;
				}
				else {
					print $this->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
					exit;
				}
			}
			else {
				switch($_FILES['Filedata']['error']) {
					case 0:
						# possible file attack
						print $this->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
						exit;
					case 1:
						# uploaded file exceeds the UPLOAD_MAX_FILESIZE directive in php.ini
						print $this->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_SIZEEXCEEDED);
						exit;
					case 2:
						# uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
						print $this->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_SIZEEXCEEDED);
						exit;
					case 3:
						# uploaded file was only partially uploaded
						print $this->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_INCOMPLETE);
						exit;
					case 4:
						print $this->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_NOFILE);
						exit;
					default:
						print $this->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
						exit;
				}
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
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
            
            $modifier = $this->_getModifierModelInstance();
            $deleted = 0;
            $rows = Sitengine_Controller_Request_Http::getSelectedRows($_POST);
            $tables = array(
                $this->getFrontController()->getBlogPackage()->getBlogsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getPostsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getFilesTableName() => 'WRITE'
            );
            
            if(sizeof($rows) > 0) {
                # lock tables
                require_once 'Sitengine/Sql.php';
                $q = Sitengine_Sql::getLockQuery($tables);
                $this->getDatabase()->getConnection()->exec($q);
                
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
                $this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
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
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
            
            $modifier = $this->_getModifierModelInstance();
            $updated = 0;
            $rows = Sitengine_Controller_Request_Http::getModifiedRows($_POST);
            $tables = array(
                $this->getFrontController()->getBlogPackage()->getBlogsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getPostsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getFilesTableName() => 'WRITE'
            );
            
            if(sizeof($rows) > 0) {
                # lock tables
                require_once 'Sitengine/Sql.php';
                $q = Sitengine_Sql::getLockQuery($tables);
                $this->getDatabase()->getConnection()->exec($q);
                
                foreach($rows as $id => $data)
                {
                    $affectedRows = $modifier->updateFromList($id, $data, $this->getFrontController()->getBlogPackage()->getAuthorizedGroups());
                    if($affectedRows > 0) { $updated++; }
                    else { $this->_markedRows[$id] = 1; }
                }
                $this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
                
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
    
    
    
    
    protected function _doBatchAssignAction()
    {
        try {
            $this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
            
            $modifier = $this->_getModifierModelInstance();
            $updated = 0;
            $rows = Sitengine_Controller_Request_Http::getSelectedRows($_POST);
            #Sitengine_Debug::print_r($rows);
            
            
            # prepare data
            $gid = $this->getPermiso()->getDirectory()->getGroupId($this->getFrontController()->getBlogPackage()->getOwnerGroup());
			$gid = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
			$date = new Zend_Date();
			$date->setTimezone('UTC');
			$cdate = $date->get(Zend_Date::ISO_8601, Sitengine_Env::LANGUAGE_EN);
            
            $tables = array(
                $this->getFrontController()->getBlogPackage()->getBlogsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getPostsTableName() => 'READ',
                $this->getFrontController()->getBlogPackage()->getFilesTableName() => 'WRITE'
            );
            
            if(sizeof($rows) > 0) {
                # lock tables
                require_once 'Sitengine/Sql.php';
                $q = Sitengine_Sql::getLockQuery($tables);
                $this->getDatabase()->getConnection()->exec($q);
                
                foreach($rows as $id => $data)
                {
                	$data[Sitengine_Permiso::FIELD_GID] = $gid;
                	$data['cdate'] = $cdate;
					$data['mdate'] = $cdate;
					$data['parentId'] = $this->getEntity()->getAncestorId();
                    $affectedRows = $modifier->assignFromList(base64_decode($id), $data);
                    if($affectedRows > 0) { $updated++; }
                    else { $this->_markedRows[$id] = 1; }
                }
                $this->getDatabase()->getConnection()->exec('UNLOCK TABLES');
                
                if($updated < sizeof($rows)) {
                    $this->getStatus()->set(
                    	Sitengine_Env::STATUS_ERRORBATCHASSIGN,
                    	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORBATCHASSIGN),
                    	true
                    );
                }
                else if(sizeof($rows) > 0) {
                    $this->getStatus()->set(
                    	Sitengine_Env::STATUS_OKBATCHASSIGN,
                    	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKBATCHASSIGN),
                    	false
                    );
                }
            }
            return $this->_goToAction(self::ACTION_ASSIGN);
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    
    protected function _doBatchUnlinkAction()
    {
        try {
            $this->_start();
        	if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            
        	require_once 'Sitengine/Http.php';
            if(!Sitengine_Http::checkReferer()) {
                require_once 'Sitengine/Blog/Frontend/Blogs/Posts/Files/Exception.php';
                throw new Sitengine_Blog_Frontend_Blogs_Posts_Files_Exception('bad request', Sitengine_Env::ERROR_BAD_REQUEST);
            }
            if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
            
            $deleted = 0;
            $rows = Sitengine_Controller_Request_Http::getSelectedRows($_POST);
            
            if(sizeof($rows) > 0) {
                
                foreach($rows as $id => $v)
                {
                	$file = $this->getTempDir().'/'.base64_decode($id);
                	if(is_writeable($file) && unlink($file)) { $deleted++; }
                    else { $this->_markedRows[$id] = 1; }
                }
                if($deleted < sizeof($rows)) {
                    $this->getStatus()->set(
                    	Sitengine_Env::STATUS_ERRORBATCHUNLINK,
                    	$this->getTranslate()->translate(Sitengine_Env::STATUS_ERRORBATCHUNLINK),
                    	true
                    );
                }
                else if(sizeof($rows) > 0) {
                    $this->getStatus()->set(
                    	Sitengine_Env::STATUS_OKBATCHUNLINK,
                    	$this->getTranslate()->translate(Sitengine_Env::STATUS_OKBATCHUNLINK),
                    	false
                    );
                }
            }
            return $this->_goToAction(self::ACTION_ASSIGN);
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
			
			if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
			
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
    
    
    
    
    protected function _insertAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
            
			$view = $this->_getDetailViewInstance();
            
			$view->controller = $this;
            
			$view->env = $this->getEnv();
            
			$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateDetailView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateDetailView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    */
    
    
    
    protected function _viewAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                #return $this->_forwardToLogin();
            }
			if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
			
			$view = $this->_getDetailViewInstance();
			
			$view->controller = $this;
			
			$view->env = $this->getEnv();
			
			$view->frontController = $this->getFrontController();
			$view->setInputMode(Sitengine_Env::INPUTMODE_UPDATE);
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateDetailView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
    		$view->build()->batchAssign($view->getData());
    		$body  = $view->render(basename($this->_templateDetailView));
    		$body .= $this->_getDebugDump($view->getData());
			$this->getResponse()->setBody($body);
			$this->getResponse()->sendResponse();
        }
        catch (Exception $exception) {
            throw $this->_prepareErrorHandler($exception);
        }
    }
    
    
    
    /*
    protected function _uploadAction()
    {
    	try {
    		$this->_start();
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
            
			$view = $this->_getUploadViewInstance();
            
			$view->controller = $this;
            
			$view->env = $this->getEnv();
            
			$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateUploadView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
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
    		if(!$this->getPermiso()->getAcl()->privateAccessGranted($this->getFrontController()->getBlogPackage()->getAuthorizedGroups())) {
                return $this->_forwardToLogin();
            }
            if(!$this->_startEntity()) { return $this->_forwardToLogin(); }
            
			$view = $this->_getAssignViewInstance();
            
			$view->controller = $this;
            
			$view->env = $this->getEnv();
            
			$view->frontController = $this->getFrontController();
    		$view->translate()->setTranslator($this->getTranslate()->getAdapter());
    		$view->setHelperPath($this->getEnv()->getIncludesDir());
    		$view->setScriptPath(dirname($this->_templateAssignView));
    		$view->doctype()->setDoctype(Zend_View_Helper_Doctype::XHTML1_STRICT);
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
    */
    
    
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