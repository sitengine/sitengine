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


require_once 'Sitengine/Controller/Front.php';


abstract class Sitengine_Proto_Backend_Front extends Sitengine_Controller_Front
{
	
	const CONTROLLER_GOODIES = 'goodies';
    const CONTROLLER_GOODIES_SHOULDIES = 'goodiesShouldies';
    const CONTROLLER_GOODIES_SHOULDIES_COULDIES = 'goodiesShouldiesCouldies';
    const CONTROLLER_LOGIN = 'login';
    const CONTROLLER_ERROR = 'error';
    const ROUTE_LOGIN = 'login';
    const ROUTE_GOODIES = 'goodies';
    const ROUTE_GOODIES_NEW = 'goodiesNew';
    const ROUTE_GOODIES_BATCH = 'goodiesBatch';
    const ROUTE_GOODIES_SHARP = 'goodiesSharp';
    const ROUTE_GOODIES_UPLOAD = 'goodiesUpload';
    const ROUTE_GOODIES_ASSIGN = 'goodiesAssign';
    const ROUTE_GOODIES_SHOULDIES = 'goodiesShouldies';
    const ROUTE_GOODIES_SHOULDIES_NEW = 'goodiesShouldiesNew';
    const ROUTE_GOODIES_SHOULDIES_BATCH = 'goodiesShouldiesBatch';
    const ROUTE_GOODIES_SHOULDIES_SHARP = 'goodiesShouldiesSharp';
    const ROUTE_GOODIES_SHOULDIES_UPLOAD = 'goodiesShouldiesUpload';
    const ROUTE_GOODIES_SHOULDIES_ASSIGN = 'goodiesShouldiesAssign';
    const ROUTE_GOODIES_SHOULDIES_COULDIES = 'goodiesShouldiesCouldies';
    const ROUTE_GOODIES_SHOULDIES_COULDIES_NEW = 'goodiesShouldiesCouldiesNew';
    const ROUTE_GOODIES_SHOULDIES_COULDIES_BATCH = 'goodiesShouldiesCouldiesBatch';
    const ROUTE_GOODIES_SHOULDIES_COULDIES_SHARP = 'goodiesShouldiesCouldiesSharp';
    const ROUTE_GOODIES_SHOULDIES_COULDIES_UPLOAD = 'goodiesShouldiesCouldiesUpload';
    const ROUTE_GOODIES_SHOULDIES_COULDIES_ASSIGN = 'goodiesShouldiesCouldiesAssign';
    
    
    
    
    
    
    protected $_permisoPackage = null;
    
    public function getPermisoPackage()
    {
    	if($this->_permisoPackage === null) {
    		$this->_permisoPackage = $this->_getPermisoPackageInstance();
    	}
    	return $this->_permisoPackage;
    }
    
    abstract protected function _getPermisoPackageInstance();
    
    
    
    
    
    
    protected $_protoPackage = null;
    
    public function getProtoPackage()
    {
    	if($this->_protoPackage === null) {
    		$this->_protoPackage = $this->_getProtoPackageInstance();
    	}
    	return $this->_protoPackage;
    }
    
    abstract protected function _getProtoPackageInstance();
    
    
    
    
    
    public function start(Sitengine_Env $env, $configName = null)
    {
    	if(!$env instanceof Sitengine_Env_Default) {
    		require_once 'Sitengine/Proto/Backend/Exception.php';
    		$message = 'env must be an instance of Sitengine_Env_Default';
    		throw new Sitengine_Proto_Backend_Exception($message);
    	}
    	if($configName === null)
    	{
    		# set default
    		$configName = 'proto';
    	}
    	return parent::start($env, $configName);
    }
    
    
    
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(!isset($config->{$this->getConfigName()}->backend->frontController))
    	{
    		require_once 'Sitengine/Proto/Backend/Exception.php';
    		$message = "front controller: {$this->getConfigName()} config not found";
    		throw new Sitengine_Proto_Backend_Exception($message);
    	}
    	
    	$config = $config->{$this->getConfigName()}->backend->frontController;
    	
    	if(
    		isset($config->servicePath) &&
			isset($config->goodiesController) &&
			isset($config->goodiesShouldiesController) &&
			isset($config->goodiesShouldiesCouldiesController) &&
			isset($config->loginController) &&
			isset($config->errorController)
		)
		{
			$this->setServicePath($config->servicePath);
			
			$this->_controllers = array(
				self::CONTROLLER_GOODIES => $config->goodiesController,
				self::CONTROLLER_GOODIES_SHOULDIES => $config->goodiesShouldiesController,
				self::CONTROLLER_GOODIES_SHOULDIES_COULDIES => $config->goodiesShouldiesCouldiesController,
				self::CONTROLLER_LOGIN => $config->loginController,
				self::CONTROLLER_ERROR => $config->errorController
			);
		}
		else {
			require_once 'Sitengine/Proto/Backend/Exception.php';
    		$message = 'front controller config error';
    		throw new Sitengine_Proto_Backend_Exception($message);
		}
    }
    
    
    
    protected function _getRouterInstance()
    {
    	$path = $this->getServicePath();
    	
    	$routes = array(
			'default' 					=> $this->_getRoute('*', self::CONTROLLER_GOODIES),
			self::ROUTE_LOGIN 			=> $this->_getRoute("$path/login", self::CONTROLLER_LOGIN),
			
			self::ROUTE_GOODIES 		=> $this->_getRoute("$path/goodies", self::CONTROLLER_GOODIES),
			self::ROUTE_GOODIES_SHARP 	=> $this->_getRoute("$path/goodies/:id", self::CONTROLLER_GOODIES),
			self::ROUTE_GOODIES_NEW 	=> $this->_getRoute("$path/goodies/new", self::CONTROLLER_GOODIES),
			self::ROUTE_GOODIES_BATCH 	=> $this->_getRoute("$path/goodies/batch", self::CONTROLLER_GOODIES),
			self::ROUTE_GOODIES_UPLOAD 	=> $this->_getRoute("$path/goodies/upload", self::CONTROLLER_GOODIES),
			self::ROUTE_GOODIES_ASSIGN 	=> $this->_getRoute("$path/goodies/assign", self::CONTROLLER_GOODIES),
			
			self::ROUTE_GOODIES_SHOULDIES 			=> $this->_getRoute("$path/goodies/:aid/shouldies", self::CONTROLLER_GOODIES_SHOULDIES),
			self::ROUTE_GOODIES_SHOULDIES_SHARP		=> $this->_getRoute("$path/goodies/:aid/shouldies/:id", self::CONTROLLER_GOODIES_SHOULDIES),
			self::ROUTE_GOODIES_SHOULDIES_NEW 		=> $this->_getRoute("$path/goodies/:aid/shouldies/new", self::CONTROLLER_GOODIES_SHOULDIES),
			self::ROUTE_GOODIES_SHOULDIES_BATCH 	=> $this->_getRoute("$path/goodies/:aid/shouldies/batch", self::CONTROLLER_GOODIES_SHOULDIES),
			self::ROUTE_GOODIES_SHOULDIES_UPLOAD 	=> $this->_getRoute("$path/goodies/:aid/shouldies/upload", self::CONTROLLER_GOODIES_SHOULDIES),
			self::ROUTE_GOODIES_SHOULDIES_ASSIGN 	=> $this->_getRoute("$path/goodies/:aid/shouldies/assign", self::CONTROLLER_GOODIES_SHOULDIES),
			
			self::ROUTE_GOODIES_SHOULDIES_COULDIES 			=> $this->_getRoute("$path/goodies/:gaid/shouldies/:aid/couldies", self::CONTROLLER_GOODIES_SHOULDIES_COULDIES),
			self::ROUTE_GOODIES_SHOULDIES_COULDIES_SHARP 	=> $this->_getRoute("$path/goodies/:gaid/shouldies/:aid/couldies/:id", self::CONTROLLER_GOODIES_SHOULDIES_COULDIES),
			self::ROUTE_GOODIES_SHOULDIES_COULDIES_NEW 		=> $this->_getRoute("$path/goodies/:gaid/shouldies/:aid/couldies/new", self::CONTROLLER_GOODIES_SHOULDIES_COULDIES),
			self::ROUTE_GOODIES_SHOULDIES_COULDIES_BATCH 	=> $this->_getRoute("$path/goodies/:gaid/shouldies/:aid/couldies/batch", self::CONTROLLER_GOODIES_SHOULDIES_COULDIES),
			self::ROUTE_GOODIES_SHOULDIES_COULDIES_UPLOAD 	=> $this->_getRoute("$path/goodies/:gaid/shouldies/:aid/couldies/upload", self::CONTROLLER_GOODIES_SHOULDIES_COULDIES),
			self::ROUTE_GOODIES_SHOULDIES_COULDIES_ASSIGN 	=> $this->_getRoute("$path/goodies/:gaid/shouldies/:aid/couldies/assign", self::CONTROLLER_GOODIES_SHOULDIES_COULDIES)
		);
		require_once 'Zend/Controller/Router/Rewrite.php';
		$router = new Zend_Controller_Router_Rewrite();
		$router->removeDefaultRoutes();
		$router->addRoutes($routes);
		return $router;
    }
    
    
    
    protected function _getRoute($uri, $controller)
    {
    	$defaults = array(
			Sitengine_Env::PARAM_CONTROLLER => $this->getController($controller),
			Sitengine_Env::PARAM_ACTION => 'factory'
		);
		require_once 'Sitengine/Controller/Router/Route.php';
    	$route = new Sitengine_Controller_Router_Route($uri, $defaults);
    	return $route->setRepresentationParam(Sitengine_Env::PARAM_REPRESENTATION);
    }
    
    
    
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
        $queries['signOut'] = $this->getRequest()->getBasePath().'/backend/proto/goodies'.Sitengine_Controller_Request_Http::makeNameValueQuery($args);
        $queries['backendHome'] = $this->getEnv()->getMyProjectRequestDir().'/backend/home';
        $queries['protoBackendGoodies'] = $this->getRequest()->getBasePath().'/backend/proto/goodies';
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
        
        $location = 'protoBackendGoodies';
        $items[$queries[$location]] = '> '.$translate->translate('labels'.ucfirst($location));
        $selected = ($current=='protoBackendGoodies') ? $queries[$location] : $selected;
        
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