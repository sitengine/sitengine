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


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_Blog_Frontend_Front extends Sitengine_Controller_Front
{
	
	const CONTROLLER_BLOGS = 'blogs';
    const CONTROLLER_BLOGS_POSTS = 'blogsPosts';
    const CONTROLLER_BLOGS_POSTS_COMMENTS = 'blogsPostsComments';
    const CONTROLLER_BLOGS_POSTS_FILES = 'blogsPostsFiles';
    const CONTROLLER_LOGIN = 'login';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_BLOGS = 'blogs';
    const ROUTE_BLOGS_NEW = 'blogsNew';
    const ROUTE_BLOGS_BATCH = 'blogsBatch';
    const ROUTE_BLOGS_SHARP = 'blogsSharp';
    const ROUTE_LOGIN = 'blogsLogin';
    const ROUTE_BLOGS_POSTS = 'blogsPosts';
    const ROUTE_BLOGS_POSTS_NEW = 'blogsPostsNew';
    const ROUTE_BLOGS_POSTS_BATCH = 'blogsPostsBatch';
    const ROUTE_BLOGS_POSTS_SHARP = 'blogsPostsSharp';
    const ROUTE_BLOGS_POSTS_COMMENTS = 'blogsPostsComments';
    #const ROUTE_BLOGS_POSTS_COMMENTS_NEW = 'blogsPostsCommentsNew';
    const ROUTE_BLOGS_POSTS_COMMENTS_BATCH = 'blogsPostsCommentsBatch';
    const ROUTE_BLOGS_POSTS_COMMENTS_SHARP = 'blogsPostsCommentsSharp';
    const ROUTE_BLOGS_POSTS_COMMENTS_LOGIN = 'blogsPostsCommentsLogin';
    const ROUTE_BLOGS_POSTS_FILES = 'blogsPostsFiles';
    const ROUTE_BLOGS_POSTS_FILES_NEW = 'blogsPostsFilesNew';
    const ROUTE_BLOGS_POSTS_FILES_BATCH = 'blogsPostsFilesBatch';
    const ROUTE_BLOGS_POSTS_FILES_SHARP = 'blogsPostsFilesSharp';
    const ROUTE_BLOGS_POSTS_FILES_UPLOAD = 'blogsPostsFilesUpload';
    #const ROUTE_BLOGS_POSTS_FILES_ASSIGN = 'blogsPostsFilesAssign';
    
    
    
    protected $_blogPackage = null;
    
    public function getBlogPackage()
    {
    	if($this->_blogPackage === null) {
    		$this->_blogPackage = $this->_getBlogPackageInstance();
    	}
    	return $this->_blogPackage;
    }
    
    abstract protected function _getBlogPackageInstance();
    
    
    
    
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/Blog/Frontend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_Blog_Frontend_Exception($message);
    	}
    	if($configName === null)
    	{
    		# set default
    		$configName = 'blog';
    	}
    	return parent::start($env, $configName);
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(!isset($config->{$this->getConfigName()}->frontend->frontController))
    	{
    		require_once 'Sitengine/Blog/Frontend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_Blog_Frontend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->frontend->frontController;
    	
    	if(
			isset($config->servicePath) &&
			isset($config->blogsController) &&
			isset($config->blogsPostsController) &&
			isset($config->blogsPostsCommentsController) &&
			isset($config->blogsPostsFilesController) &&
			isset($config->loginController) &&
			isset($config->errorController)
		)
		{
			$this->setServicePath($config->servicePath);
			$this->_controllers = array(
				self::CONTROLLER_BLOGS => $config->blogsController,
				self::CONTROLLER_BLOGS_POSTS => $config->blogsPostsController,
				self::CONTROLLER_BLOGS_POSTS_COMMENTS => $config->blogsPostsCommentsController,
				self::CONTROLLER_BLOGS_POSTS_FILES => $config->blogsPostsFilesController,
				self::CONTROLLER_LOGIN => $config->loginController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/Blog/Frontend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_Blog_Frontend_Exception($message);
		}
    }
    
    
    
    
    
    protected function _getRouterInstance()
    {
    	$routes
    		= ($this->getBlogPackage()->getBlogSlug() === null)
    		? $this->_getMultiStreamRoutes()
    		: $this->_getSingleStreamRoutes()
    	;
    	
		require_once 'Zend/Controller/Router/Rewrite.php';
		$router = new Zend_Controller_Router_Rewrite();
		$router->removeDefaultRoutes();
		$router->addRoutes($routes);
		return $router;
    }
    
    
    
    
    
    
    
    
    protected function _getMultiStreamRoutes()
    {
    	$path = $this->getServicePath();
    	
    	return array(
			'default' 					=> $this->_getRoute('*', self::CONTROLLER_BLOGS),
			self::ROUTE_LOGIN 			=> $this->_getRoute("$path/login", self::CONTROLLER_LOGIN),
			self::ROUTE_BLOGS 			=> $this->_getRoute("$path", self::CONTROLLER_BLOGS),
			
			self::ROUTE_BLOGS_POSTS 			=> $this->_getRoute("$path/:aid/posts", self::CONTROLLER_BLOGS_POSTS),
			self::ROUTE_BLOGS_POSTS_SHARP		=> $this->_getRoute("$path/:aid/posts/:id", self::CONTROLLER_BLOGS_POSTS),
			
			self::ROUTE_BLOGS_POSTS_COMMENTS 		=> $this->_getRoute("$path/:gaid/posts/:aid/comments", self::CONTROLLER_BLOGS_POSTS_COMMENTS),
			self::ROUTE_BLOGS_POSTS_COMMENTS_SHARP 	=> $this->_getRoute("$path/:gaid/posts/:aid/comments/:id", self::CONTROLLER_BLOGS_POSTS_COMMENTS),
			#self::ROUTE_BLOGS_POSTS_COMMENTS_NEW 	=> $this->_getRoute("$path/:gaid/posts/:aid/comment", self::CONTROLLER_BLOGS_POSTS_COMMENTS),
			#self::ROUTE_BLOGS_POSTS_COMMENTS_LOGIN 	=> $this->_getRoute("$path/:gaid/posts/:aid/comments/login", self::CONTROLLER_BLOGS_POSTS_COMMENTS),
			
			self::ROUTE_BLOGS_POSTS_FILES 			=> $this->_getRoute("$path/:gaid/posts/:aid/photos", self::CONTROLLER_BLOGS_POSTS_FILES),
			self::ROUTE_BLOGS_POSTS_FILES_SHARP 	=> $this->_getRoute("$path/:gaid/posts/:aid/photos/:id", self::CONTROLLER_BLOGS_POSTS_FILES),
		);
	}
	
	
	
	
	
	
	
	
	protected function _getSingleStreamRoutes()
    {
    	$path = $this->getServicePath();
    	$blogSlug = $this->getBlogPackage()->getBlogSlug();
    	
    	require_once 'Zend/Controller/Router/Route.php';
		$route = new Zend_Controller_Router_Route(
			'*',
			array(
				Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_BLOGS_POSTS),
				Sitengine_Env::PARAM_ACTION => 'restMapper',
				Sitengine_Env::PARAM_ANCESTORID => $blogSlug
			)
		);
		
		return array(
			'default' 								=> $route,
			self::ROUTE_BLOGS 						=> $route,
			self::ROUTE_LOGIN 						=> $route,
			self::ROUTE_BLOGS_POSTS_COMMENTS 		=> $route,
			self::ROUTE_BLOGS_POSTS 				=> $this->_getRoute(":aid", self::CONTROLLER_BLOGS_POSTS),
			self::ROUTE_BLOGS_POSTS_SHARP			=> $this->_getRoute(":aid/:id", self::CONTROLLER_BLOGS_POSTS),
			self::ROUTE_BLOGS_POSTS_COMMENTS 		=> $this->_getRoute(":gaid/:aid/comments", self::CONTROLLER_BLOGS_POSTS_COMMENTS),
			self::ROUTE_BLOGS_POSTS_COMMENTS_SHARP 	=> $this->_getRoute(":gaid/:aid/comments/:id", self::CONTROLLER_BLOGS_POSTS_COMMENTS),
			#self::ROUTE_BLOGS_POSTS_COMMENTS_NEW 	=> $this->_getRoute(":gaid/:aid/comment", self::CONTROLLER_BLOGS_POSTS_COMMENTS),
			self::ROUTE_BLOGS_POSTS_FILES 			=> $this->_getRoute(":gaid/:aid/photos", self::CONTROLLER_BLOGS_POSTS_FILES),
			self::ROUTE_BLOGS_POSTS_FILES_SHARP 	=> $this->_getRoute(":gaid/:aid/photos/:id", self::CONTROLLER_BLOGS_POSTS_FILES),
		);
	}
    
    
    
    
    
    
    
    
    
    
    
    
    
    protected function _getRoute($uri, $controller)
    {
    	$defaults = array(
			Sitengine_Env::PARAM_CONTROLLER => $this->getController($controller),
			Sitengine_Env::PARAM_ACTION => 'restMapper'
		);
		require_once 'Sitengine/Controller/Router/Route.php';
    	$route = new Sitengine_Controller_Router_Route($uri, $defaults);
    	return $route->setRepresentationParam(Sitengine_Env::PARAM_REPRESENTATION);
    }
    
    
    
    
    protected $_permiso = null;
    
    public function getPermiso()
    {
    	if($this->_permiso === null) {
    		$this->_permiso = $this->_getPermisoInstance();
    	}
    	return $this->_permiso;
    }
    
    abstract protected function _getPermisoInstance();
    
    
    
    
    protected function _getErrorHandlerInstance()
    {
    	require_once 'Zend/Controller/Plugin/ErrorHandler.php';
		$plugin = new Zend_Controller_Plugin_ErrorHandler();
		$plugin->setErrorHandlerController($this->getController(self::CONTROLLER_ERROR));
		return $plugin;
    }
    
    
    
    
    
    
    public function getQueries()
    {
    	$queries = array();
    	
    	$args = array(
            Sitengine_Env::PARAM_LOGOUT => 1
        );
        $queries['signOut'] = $this->getRequest()->getBasePath().'/backend/blogs'.Sitengine_Controller_Request_Http::makeNameValueQuery($args);
        $queries['backendHome'] = $this->getEnv()->getMyProjectRequestDir().'/backend/home';
        $queries['blogFrontendBlogs'] = $this->getRequest()->getBasePath().'/backend/blogs';
        return $queries;
    }
    
    
    
    
    
    
    public function getGlobalNavSection(
        Sitengine_Translate $translate,
        array $queries,
        $current,
        $selectorPrefix=''
    )
    {
        $selected = '';
        $items = array();
        
        $location = 'backendHome';
        $items[$queries[$location]] = '> '.$translate->translate('labels'.ucfirst($location));
        
        $items['separator10'] = '----------------';
        
        $location = 'blogFrontendBlogs';
        $items[$queries[$location]] = '> '.$translate->translate('labels'.ucfirst($location));
        $selected = ($current=='blogFrontendBlogs') ? $queries[$location] : $selected;
        
        $n = 'globalNav';
        require_once 'Sitengine/Form/Element.php';
        $e = new Sitengine_Form_Element('', $selected);
        $e->setScript('onchange="if(!this.options[this.selectedIndex].value.match(/^separator/)) { window.location=this.options[this.selectedIndex].value; }"');
        $e->setClass($selectorPrefix.'Select');
        $e->setId($selectorPrefix.$n);
        
        return array(
            'ITEMS' => $items,
            'menu' => $e->getSelect($items)
        );
    }

}
?>