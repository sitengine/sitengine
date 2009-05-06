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


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_Sitemap_Backend_Front extends Sitengine_Controller_Front
{
	
	const CONTROLLER_INDEX = 'index';
    const CONTROLLER_LOGIN = 'login';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_INDEX = 'index';
    const ROUTE_LOGIN = 'login';
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/Sitemap/Backend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_Sitemap_Backend_Exception($message);
    	}
    	return parent::start($env, $configName);
    }
    
    
    
    protected $_sitemapPackage = null;
    
    public function getSitemapPackage()
    {
    	if($this->_sitemapPackage === null) {
    		$this->_sitemapPackage = $this->_getSitemapPackageInstance();
    	}
    	return $this->_sitemapPackage;
    }
    
    abstract protected function _getSitemapPackageInstance();
    
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	$config = $config->sitemapBackendFrontController;
    	
    	if(
			isset($config->indexController) &&
			isset($config->loginController) &&
			isset($config->errorController)
		)
		{
			$this->_controllers = array(
				self::CONTROLLER_INDEX => $config->indexController,
				self::CONTROLLER_LOGIN => $config->loginController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/Sitemap/Backend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_Sitemap_Backend_Exception($message);
		}
    }
    
    
    
    protected function _getRouterInstance()
    {
    	require_once 'Zend/Controller/Router/Route.php';
    	
    	$routes = array(
			'default' => new Zend_Controller_Router_Route(
				'*',
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_INDEX),
					Sitengine_Env::PARAM_ACTION => 'index'
				)
			),
			self::ROUTE_INDEX => new Zend_Controller_Router_Route(
				'backend/sitemap/:'.Sitengine_Env::PARAM_ACTION.'/*',
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_INDEX),
					Sitengine_Env::PARAM_ACTION => 'index'
				)
			),
			self::ROUTE_LOGIN => new Zend_Controller_Router_Route(
				'backend/sitemap/login',
				array(
					Sitengine_Env::PARAM_CONTROLLER => $this->getController(self::CONTROLLER_LOGIN),
					Sitengine_Env::PARAM_ACTION => 'index'
				)
			)
		);
		require_once 'Zend/Controller/Router/Rewrite.php';
		$router = new Zend_Controller_Router_Rewrite();
		$router->removeDefaultRoutes();
		$router->addRoutes($routes);
		return $router;
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
    
    
    
    public function getQueries(Sitengine_Permiso $permiso)
    {
    	$queries = array();
    	
    	require_once 'Zend/Controller/Router/Route/Module.php';
    	$route = new Zend_Controller_Router_Route_Module(array(), $this->getDispatcher(), $this->getRequest());
    	
    	$args = array(
            #Sitengine_Env::PARAM_ORG => $permiso->getOrganization()->getNameNoDefault()
        );
        $queries['backendHome'] = $this->getEnv()->getMyProjectRequestDir().'/backend/home/'.$route->assemble($args , true);
        
        
    	$args = array(
    		#Sitengine_Env::PARAM_ORG => $permiso->getOrganization()->getNameNoDefault(),
            Sitengine_Env::PARAM_LOGOUT => 1
        );
        $queries['signOut'] = $this->getRequest()->getBasePath().'/backend/home'.Sitengine_Controller_Request_Http::makeNameValueQuery($args);
        
        
        $args = array(
            #Sitengine_Env::PARAM_ORG => $permiso->getOrganization()->getNameNoDefault()
        );
        $queries['sitemapBackend'] = $this->getRequest()->getBasePath().'/backend/sitemap/'.$route->assemble($args , true);
        
        return $queries;
    }
    
    
    
    
    
    
    
    public function getGlobalNavSection(
        Sitengine_Permiso $permiso,
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
        $selected = ($current==$location) ? $queries[$location] : $selected;
        
        $items['separator50'] = '----------------';
        
        $location = 'sitemapBackend';
        $items[$queries[$location]] = '> '.$translate->translate('labels'.ucfirst($location));
        $selected = ($current==$location) ? $queries[$location] : $selected;
        
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